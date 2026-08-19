<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $forPos = $request->boolean('pos');
        $query = Product::query();

        if ($forPos) {
            // POS needs only catalog fields + current stock — skip heavy relations
            $query->with([]);
        } else {
            $query->with(['productGroup', 'barcodes', 'taxes', 'stocks.warehouse', 'branchInventories.branch']);
        }

        $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhere('is_global', true);
        });

        if ($request->filled('product_group_id')) {
            $query->where('product_group_id', $request->product_group_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('code', 'ilike', "%{$term}%")
                  ->orWhereHas('barcodes', fn($b) => $b->where('value', 'ilike', "%{$term}%"));
            });
        }

        $perPage = $request->filled('per_page') ? (int) $request->per_page : 25;
        $products = $query->orderBy('name')->paginate($perPage);

        if ($forPos) {
            // Attach current stock from the active branch (multi-branch) or the
            // default warehouse (single mode / no active branch) — same as pos-summary.
            $branchId = $request->header('X-Active-Branch');
            if ($branchId && !\App\Services\SystemModeService::isSingleMode() && auth()->user()->canAccessBranch($branchId)) {
                $stockMap = \App\Models\BranchInventory::where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->get(['product_id', 'stock'])
                    ->keyBy('product_id');
                $products->getCollection()->transform(function ($p) use ($stockMap) {
                    $s = $stockMap->get($p->id);
                    return $this->posProductShape($p, $s ? (float) $s->stock : 0);
                });
            } else {
                $warehouseId = \App\Models\Warehouse::where('tenant_id', $tenantId)->where('is_default', true)->value('id');
                $stockMap = $warehouseId
                    ? \App\Models\Stock::where('tenant_id', $tenantId)->where('warehouse_id', $warehouseId)->get(['product_id', 'quantity'])->keyBy('product_id')
                    : collect();
                $products->getCollection()->transform(function ($p) use ($stockMap) {
                    $s = $stockMap->get($p->id);
                    return $this->posProductShape($p, $s ? (float) $s->quantity : 0);
                });
            }
        }

        return response()->json(['data' => $products]);
    }

    private function posProductShape(Product $p, float $stock): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'price' => (float) $p->price,
            'measurement_unit' => $p->measurement_unit,
            'product_group_id' => $p->product_group_id,
            'track_inventory' => $p->track_inventory,
            'image' => $p->image,
            'color' => $p->color,
            'is_service' => $p->is_service,
            'is_global' => $p->is_global,
            'is_enabled' => $p->is_enabled,
            'current_stock' => $stock,
        ];
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'              => 'required|max:255',
            'code'              => 'nullable|string',
            'plu'               => 'nullable|integer',
            'price'             => 'nullable|numeric',
            'mrp'               => 'nullable|numeric',
            'cost'              => 'nullable|numeric',
            'product_group_id'  => "nullable|exists:product_groups,id,tenant_id,$tenantId",
            'track_inventory'   => 'boolean',
            'is_global'         => 'boolean',
        ]);

        try {
            $data = $request->only([
                'name', 'code', 'plu', 'price', 'mrp', 'cost', 'product_group_id',
                'track_inventory', 'is_service', 'measurement_unit', 'color', 'image', 'is_enabled',
            ]);
            // Only admins may mark products as shared across tenants.
            if (auth()->user()->access_level < 9) {
                $data['is_global'] = false;
            }
            $data['tenant_id'] = $tenantId;
            $product = Product::create($data);

            if ($request->filled('barcode')) {
                Barcode::create([
                    'product_id' => $product->id,
                    'value' => $request->barcode,
                    'barcode_type' => $request->barcode_type ?? 'CODE_128',
                    'is_primary' => true,
                ]);
            }

            $product->load('barcodes');
            return response()->json(['data' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product'], 500);
        }
    }

    public function show($id)
    {
        $product = Product::with(['productGroup', 'barcodes', 'taxes'])
            ->where(function ($q) {
                $q->where('tenant_id', auth()->user()->tenant_id)->orWhere('is_global', true);
            })
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['data' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('tenant_id', auth()->user()->tenant_id)->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'              => 'sometimes|required|max:255',
            'code'              => 'nullable|string',
            'plu'               => 'nullable|integer',
            'price'             => 'nullable|numeric',
            'mrp'               => 'nullable|numeric',
            'cost'              => 'nullable|numeric',
            'product_group_id'  => "nullable|exists:product_groups,id,tenant_id,$tenantId",
        ]);

        try {
            $data = $request->only([
                'name', 'code', 'plu', 'price', 'mrp', 'cost', 'product_group_id',
                'track_inventory', 'is_service', 'measurement_unit', 'color', 'image', 'is_enabled',
            ]);
            if (auth()->user()->access_level < 9) {
                unset($data['is_global']);
            } elseif ($request->has('is_global')) {
                $data['is_global'] = $request->boolean('is_global');
            }
            $product->update($data);

            if ($request->filled('barcode')) {
                $existing = Barcode::where('product_id', $product->id)->where('is_primary', true)->first();
                if ($existing && $existing->value !== $request->barcode) {
                    $existing->update(['value' => $request->barcode, 'barcode_type' => $request->barcode_type ?? 'CODE_128']);
                } elseif (!$existing) {
                    Barcode::create([
                        'product_id' => $product->id,
                        'value' => $request->barcode,
                        'barcode_type' => $request->barcode_type ?? 'CODE_128',
                        'is_primary' => true,
                    ]);
                }
            }

            $product->load('barcodes');
            return response()->json(['data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product'], 500);
        }
    }

    public function nextCode(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $request->validate(['product_group_id' => "required|uuid|exists:product_groups,id,tenant_id,$tenantId"]);

        $group = ProductGroup::where('tenant_id', $tenantId)->find($request->product_group_id);
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $group->name), 0, 3));

        $lastCode = Product::where('tenant_id', $tenantId)
            ->where('code', 'LIKE', $prefix . '%')
            ->whereRaw("code ~ ?", ['^' . $prefix . '[0-9]+$'])
            ->orderByRaw('LENGTH(code) DESC, code DESC')
            ->value('code');

        $next = $lastCode
            ? $prefix . str_pad((int) substr($lastCode, strlen($prefix)) + 1, 3, '0', STR_PAD_LEFT)
            : $prefix . '001';

        return response()->json(['data' => ['code' => $next]]);
    }

    public function destroy($id)
    {
        $product = Product::where('tenant_id', auth()->user()->tenant_id)->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $product->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
    }
}
