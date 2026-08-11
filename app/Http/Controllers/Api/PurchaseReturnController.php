<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementType;
use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseReturn::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['supplier:id,name', 'purchase:id,purchase_number'])
            ->orderBy('return_date', 'desc');

        if ($purchaseId = $request->query('purchase_id')) {
            $query->where('purchase_id', $purchaseId);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_id' => 'required|uuid|exists:purchases,id',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
            'return_date' => 'required|date',
            'reason'      => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|uuid|exists:purchase_items,id',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.reason'     => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $purchase = Purchase::where('tenant_id', $tenantId)->findOrFail($validated['purchase_id']);

        if (!in_array($purchase->status, ['received', 'partially_received'])) {
            return response()->json(['message' => 'Only received purchases can be returned'], 422);
        }

        $purchaseReturn = DB::transaction(function () use ($validated, $tenantId, $purchase) {
            $items = $validated['items'];
            unset($validated['items']);

            $validated['tenant_id'] = $tenantId;
            $validated['supplier_id'] = $purchase->supplier_id;
            $validated['return_number'] = PurchaseReturn::generateNumber();
            $validated['created_by'] = auth()->id();
            $validated['status'] = 'completed';

            if (empty($validated['warehouse_id'])) {
                $validated['warehouse_id'] = $purchase->warehouse_id;
            }

            $subtotal = 0;
            $taxTotal = 0;

            $purchaseReturn = PurchaseReturn::create($validated);

            foreach ($items as $ri) {
                $purchaseItem = $purchase->items()->findOrFail($ri['purchase_item_id']);
                $qty = min($ri['quantity'], $purchaseItem->received_quantity);

                $lineTotal = bcmul($qty, $ri['unit_cost'], 4);
                $subtotal = bcadd($subtotal, $lineTotal, 4);

                $itemTax = $purchaseItem->tax_amount > 0
                    ? bcmul(bcdiv($purchaseItem->tax_amount, $purchaseItem->quantity, 4), $qty, 4)
                    : 0;
                $taxTotal = bcadd($taxTotal, $itemTax, 4);

                PurchaseReturnItem::create([
                    'return_id'        => $purchaseReturn->id,
                    'purchase_item_id' => $ri['purchase_item_id'],
                    'product_id'       => $ri['product_id'],
                    'quantity'         => $qty,
                    'unit_cost'        => $ri['unit_cost'],
                    'total'            => $lineTotal,
                    'reason'           => $ri['reason'] ?? null,
                ]);

                // Reverse stock
                $warehouseId = $purchaseReturn->warehouse_id;
                $stock = Stock::where('product_id', $ri['product_id'])
                    ->where('warehouse_id', $warehouseId)->first();
                if ($stock) {
                    $stock->decrement('quantity', $qty);
                    $stock->increment('version');
                    StockMovement::create([
                        'tenant_id'       => $tenantId,
                        'product_id'      => $ri['product_id'],
                        'warehouse_id'    => $warehouseId,
                        'movement_type'   => MovementType::Refund->value,
                        'quantity_change' => -$qty,
                        'quantity_after'  => $stock->fresh()->quantity,
                        'reference_type'  => 'purchase_return',
                        'reference_id'    => $purchaseReturn->id,
                        'user_id'         => auth()->id(),
                    ]);
                }

                if (!SystemModeService::isSingleMode() && $purchase->branch_id) {
                    $branchInv = BranchInventory::where('product_id', $ri['product_id'])
                        ->where('branch_id', $purchase->branch_id)->first();
                    if ($branchInv) {
                        $branchInv->updateStock(-$qty);
                    }
                }

                $purchaseItem->decrement('received_quantity', $qty);
            }

            $grandTotal = bcadd($subtotal, $taxTotal, 4);
            $purchaseReturn->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
            ]);

            return $purchaseReturn;
        });

        $purchaseReturn->load(['items.product:id,name', 'purchase:id,purchase_number']);
        return response()->json(['data' => $purchaseReturn], 201);
    }

    public function show(string $id): JsonResponse
    {
        $return = PurchaseReturn::where('tenant_id', auth()->user()->tenant_id)
            ->with(['items.product:id,name,code', 'purchase:id,purchase_number', 'supplier:id,name'])
            ->findOrFail($id);

        return response()->json(['data' => $return]);
    }
}
