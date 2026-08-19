<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\BranchInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with([
                'product.productGroup',
                'warehouse',
            ]);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('low_stock')) {
            $query->where('quantity', '<=', $request->low_stock);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        $stocks = $query->orderBy('quantity', 'asc')->paginate($perPage);

        return response()->json(['data' => $stocks]);
    }

    public function show($id)
    {
        $stock = Stock::with([
            'product.productGroup',
            'product.stockControls',
            'warehouse',
        ])->where('tenant_id', auth()->user()->tenant_id)->find($id);

        if (!$stock) {
            return response()->json(['message' => 'Stock record not found'], 404);
        }

        $recentMovements = StockMovement::where('tenant_id', auth()->user()->tenant_id)
            ->where('product_id', $stock->product_id)
            ->where('warehouse_id', $stock->warehouse_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => [
                'stock'    => $stock,
                'movements' => $recentMovements,
            ],
        ]);
    }

    public function adjust(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'product_id'   => "required|exists:products,id,tenant_id,$tenantId",
            'warehouse_id' => "required|exists:warehouses,id,tenant_id,$tenantId",
            'new_quantity' => 'required|numeric|min:0',
            'note'         => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request, $tenantId) {
                $stock = Stock::where('tenant_id', $tenantId)
                    ->where('product_id', $request->product_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = Stock::create([
                        'tenant_id' => $tenantId,
                        'product_id' => $request->product_id,
                        'warehouse_id' => $request->warehouse_id,
                        'quantity' => 0,
                        'version' => 1,
                    ]);
                }

                $oldQuantity = $stock->quantity;
                $quantityChange = $request->new_quantity - $oldQuantity;

                $stock->quantity = $request->new_quantity;
                $stock->version += 1;
                $stock->save();

                StockMovement::create([
                    'tenant_id'       => $stock->tenant_id,
                    'product_id'      => $stock->product_id,
                    'warehouse_id'    => $stock->warehouse_id,
                    'movement_type'   => 'adjustment',
                    'quantity_change' => $quantityChange,
                    'quantity_after'  => $stock->quantity,
                    'reference_type'  => 'manual',
                    'reference_id'    => null,
                    'user_id'         => $request->user()->id,
                    'note'            => $request->note,
                ]);
            });

            $stock = Stock::where('tenant_id', $tenantId)
                ->where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->with(['product', 'warehouse'])
                ->first();

            return response()->json(['data' => $stock]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage() ?: 'Stock adjustment failed'], $e->getCode() === 0 ? 500 : 404);
        }
    }

    public function movementHistory(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'product_id'   => "nullable|exists:products,id,tenant_id,$tenantId",
            'warehouse_id' => "nullable|exists:warehouses,id,tenant_id,$tenantId",
        ]);

        $query = StockMovement::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'product',
                'warehouse',
                'user',
            ]);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json(['data' => $movements]);
    }

    public function inventoryCount(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'action'       => 'required|in:start,review,commit',
            'warehouse_id' => "required|exists:warehouses,id,tenant_id,$tenantId",
            'items'        => 'required_if:action,commit|array',
            'items.*.product_id' => "required_if:action,commit|exists:products,id,tenant_id,$tenantId",
            'items.*.counted_quantity' => 'required_if:action,commit|numeric|min:0',
        ]);

        try {
            if ($request->action === 'start') {
                $stocks = Stock::where('tenant_id', $tenantId)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->with('product')
                    ->get();

                $snapshot = $stocks->map(function ($stock) {
                    return [
                        'stock_id'        => $stock->id,
                        'product_id'      => $stock->product_id,
                        'product_name'    => $stock->product->name ?? '',
                        'product_code'    => $stock->product->code ?? '',
                        'system_quantity' => $stock->quantity,
                        'counted_quantity' => null,
                        'variance'        => null,
                    ];
                });

                return response()->json([
                    'data' => [
                        'warehouse_id' => $request->warehouse_id,
                        'status'       => 'started',
                        'snapshot'     => $snapshot,
                    ],
                ]);
            }

            if ($request->action === 'review') {
                $request->validate([
                    'items'                       => 'required|array',
                    'items.*.stock_id'            => 'required|exists:stocks,id',
                    'items.*.counted_quantity'    => 'required|numeric|min:0',
                ]);

                $items = [];
                foreach ($request->items as $item) {
                    $stock = Stock::where('tenant_id', $tenantId)->with('product')->find($item['stock_id']);
                    if (! $stock) {
                        return response()->json(['message' => 'Invalid stock item'], 422);
                    }
                    $variance = $item['counted_quantity'] - $stock->quantity;

                    $items[] = [
                        'stock_id'         => $stock->id,
                        'product_id'       => $stock->product_id,
                        'product_name'     => $stock->product->name ?? '',
                        'product_code'     => $stock->product->code ?? '',
                        'system_quantity'  => $stock->quantity,
                        'counted_quantity' => $item['counted_quantity'],
                        'variance'         => $variance,
                        'has_variance'     => abs($variance) > 0.0001,
                    ];
                }

                $totalItems = count($items);
                $varianceItems = count(array_filter($items, fn ($i) => $i['has_variance']));

                return response()->json([
                    'data' => [
                        'warehouse_id'   => $request->warehouse_id,
                        'status'         => 'reviewed',
                        'total_items'    => $totalItems,
                        'variance_items' => $varianceItems,
                        'items'          => $items,
                    ],
                ]);
            }

            if ($request->action === 'commit') {
                DB::transaction(function () use ($request, $tenantId) {
                    foreach ($request->items as $item) {
                        $stock = Stock::where('tenant_id', $tenantId)
                            ->where('product_id', $item['product_id'])
                            ->where('warehouse_id', $request->warehouse_id)
                            ->lockForUpdate()
                            ->first();

                        if ($stock && abs($item['counted_quantity'] - $stock->quantity) > 0.0001) {
                            $oldQuantity = $stock->quantity;
                            $quantityChange = $item['counted_quantity'] - $oldQuantity;

                            $stock->quantity = $item['counted_quantity'];
                            $stock->version += 1;
                            $stock->save();

                            StockMovement::create([
                                'tenant_id'       => $stock->tenant_id,
                                'product_id'      => $stock->product_id,
                                'warehouse_id'    => $stock->warehouse_id,
                                'movement_type'   => 'inventory_count',
                                'quantity_change' => $quantityChange,
                                'quantity_after'  => $stock->quantity,
                                'reference_type'  => 'inventory',
                                'reference_id'    => null,
                                'user_id'         => $request->user()->id,
                                'note'            => 'Inventory count adjustment',
                            ]);
                        }
                    }
                });

                return response()->json([
                    'data' => [
                        'warehouse_id' => $request->warehouse_id,
                        'status'       => 'committed',
                        'message'      => 'Inventory count has been committed successfully',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Inventory count operation failed'], 500);
        }
    }

    public function posSummary(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // In single company mode, always use warehouse stock (stocks table).
        // Never use branch_inventories even if X-Active-Branch header is accidentally sent.
        $branchId = \App\Services\SystemModeService::isSingleMode()
            ? null
            : $request->header('X-Active-Branch');

        $productIds = $request->input('product_ids', []);
        if (!is_array($productIds)) { $productIds = $productIds ? [$productIds] : []; }

        // Include every trackable product (even those without a stock row yet)
        // so the POS can always show its stock status (defaulting to 0).
        $productsQuery = \App\Models\Product::query()
            ->where(fn($q) => $q->where('tenant_id', $tenantId)->orWhere('is_global', true))
            ->where('track_inventory', true);
        if (!empty($productIds)) { $productsQuery->whereIn('id', $productIds); }
        $products = $productsQuery->get();

        if ($branchId) {
            $branchId = auth()->user()->canAccessBranch($branchId) ? $branchId : null;
        }

        if ($branchId) {
            $stockMap = BranchInventory::where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->get()
                ->keyBy('product_id');
        } else {
            $warehouseId = \App\Models\Warehouse::where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->value('id');
            $stockMap = $warehouseId
                ? Stock::where('tenant_id', $tenantId)->where('warehouse_id', $warehouseId)->get()->keyBy('product_id')
                : collect();
        }

        $result = [];
        foreach ($products as $product) {
            $stock = $stockMap->get($product->id);
            $qty = $stock ? (float) ($stock->quantity ?? $stock->stock ?? 0) : 0;
            $result[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $qty,
                'status' => $qty <= 0 ? 'Out of Stock' : 'In Stock',
            ];
        }

        return response()->json(['data' => $result]);
    }
public function bulkUpdate(Request $request): JsonResponse
{
    $request->validate(['items' => 'required|array|min:1', 'items.*.product_code' => 'required|string', 'items.*.quantity' => 'required|numeric|min:0']);
    $tenantId = auth()->user()->tenant_id;
        $warehouseId = $request->input('warehouse_id') ?: \App\Models\Warehouse::where('tenant_id', $tenantId)->where('is_default', true)->value('id');
    $branchCode = $request->input('branch_code');
    $branchId = null;

    if ($branchCode) {
        $branch = \App\Models\Tenant::where('company_id', $tenantId)
            ->orWhere('id', $tenantId)
            ->where(function ($q) use ($branchCode) {
                $q->where('branch_code', $branchCode)->orWhere('name', $branchCode);
            })
            ->first();
        if ($branch && auth()->user()->canAccessBranch($branch->id)) {
            $branchId = $branch->id;
        }
    }

    if (!$warehouseId && !$branchId) return response()->json(['message' => 'No warehouse or branch found'], 400);

    $updated = 0;
    foreach ($request->items as $item) {
        $product = \App\Models\Product::where('tenant_id', $tenantId)->where('code', $item['product_code'])->first();
        if (!$product) continue;
        $newQty = (float) $item['quantity'];

        if ($branchId) {
            $bi = BranchInventory::where('product_id', $product->id)->where('branch_id', $branchId)->first();
            $oldQty = $bi ? (float) $bi->stock : 0;
            BranchInventory::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branchId],
                ['tenant_id' => $tenantId, 'stock' => $newQty, 'minimum_stock' => $item['minimum'] ?? 0, 'maximum_stock' => $item['maximum'] ?? 0]
            );
        } elseif ($warehouseId) {
            $stock = Stock::where('product_id', $product->id)->where('warehouse_id', $warehouseId)->lockForUpdate()->first();
            $oldQty = $stock ? (float) $stock->quantity : 0;
            if ($stock) { $stock->quantity = $newQty; $stock->version += 1; $stock->save(); }
            else { $stock = Stock::create(['tenant_id' => $tenantId, 'product_id' => $product->id, 'warehouse_id' => $warehouseId, 'quantity' => $newQty, 'version' => 1]); }
            StockMovement::create(['tenant_id' => $tenantId, 'product_id' => $product->id, 'warehouse_id' => $warehouseId, 'movement_type' => 'adjustment', 'quantity_change' => $newQty - $oldQty, 'quantity_after' => $newQty, 'reference_type' => 'bulk_upload', 'user_id' => auth()->id(), 'note' => 'Bulk stock upload']);
        }
        $updated++;
    }
    $msg = $branchId ? "Updated branch stock for {$updated} products" : "Updated stock for {$updated} products";
    return response()->json(['message' => $msg]);
}

public function transfer(Request $request): JsonResponse
{
    $request->validate([
        'product_code' => 'required|string',
        'quantity'     => 'required|numeric|min:1',
        'from_branch'  => 'required|string',
        'to_branch'    => 'required|string|different:from_branch',
    ]);

    $tenantId = auth()->user()->tenant_id;
    $product = \App\Models\Product::where('code', $request->product_code)->where(function ($q) use ($tenantId) { $q->where('tenant_id', $tenantId)->orWhere('is_global', true); })->first();
    if (!$product) return response()->json(['message' => 'Product not found'], 404);

    $fromBranch = \App\Models\Tenant::whereIn('id', auth()->user()->allowedBranchIds())
        ->where(function ($q) use ($request) {
            $q->where('name', $request->from_branch)->orWhere('branch_code', $request->from_branch);
        })->first();
    $toBranch = \App\Models\Tenant::whereIn('id', auth()->user()->allowedBranchIds())
        ->where(function ($q) use ($request) {
            $q->where('name', $request->to_branch)->orWhere('branch_code', $request->to_branch);
        })->first();
    if (!$fromBranch || !$toBranch) return response()->json(['message' => 'Branch not found'], 404);

    $fromBI = \App\Models\BranchInventory::where('product_id', $product->id)->where('branch_id', $fromBranch->id)->first();
    $fromStock = $fromBI ? (float) $fromBI->stock : 0;
    $qty = (float) $request->quantity;

    if ($fromStock < $qty) return response()->json(['message' => "Insufficient stock at {$fromBranch->name}. Available: {$fromStock}"], 422);

    \Illuminate\Support\Facades\DB::transaction(function () use ($product, $fromBranch, $toBranch, $qty, $tenantId) {
        $fromBI = \App\Models\BranchInventory::where('product_id', $product->id)->where('branch_id', $fromBranch->id)->first();
        if ($fromBI) { $fromBI->stock -= $qty; $fromBI->save(); }
        $toBI = \App\Models\BranchInventory::firstOrCreate(
            ['product_id' => $product->id, 'branch_id' => $toBranch->id],
            ['tenant_id' => $tenantId, 'stock' => 0, 'minimum_stock' => 0, 'maximum_stock' => 0]
        );
        $toBI->stock += $qty; $toBI->save();

        \App\Models\StockTransfer::create([
            'tenant_id' => $tenantId,
            'from_branch_id' => $fromBranch->id,
            'to_branch_id' => $toBranch->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ]);
    });

    return response()->json(['message' => "Transferred {$qty} of {$product->name} from {$fromBranch->name} to {$toBranch->name}"]);
}
}
