<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementType;
use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');

        $query = Purchase::query()
            ->where('tenant_id', $tenantId)
            ->with(['supplier:id,name,code', 'warehouse:id,name'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('purchase_number', 'desc');

        if ($branchId && !SystemModeService::isSingleMode()) {
            $query->where('branch_id', $branchId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'ilike', "%{$search}%")
                  ->orWhere('reference_number', 'ilike', "%{$search}%");
            });
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->where('purchase_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $query->where('purchase_date', '<=', $dateTo);
        }

        $purchases = $query->paginate($request->query('per_page', 20));

        return response()->json($purchases);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'supplier_id'    => "nullable|uuid|exists:customers,id,tenant_id,$tenantId",
            'warehouse_id'   => "nullable|uuid|exists:warehouses,id,tenant_id,$tenantId",
            'branch_id'      => 'nullable|uuid|exists:tenants,id',
            'reference_number' => 'nullable|string|max:100',
            'purchase_date'  => 'required|date',
            'expected_date'  => 'nullable|date',
            'discount'       => 'nullable|numeric|min:0',
            'discount_type'  => 'nullable|integer|in:0,1',
            'shipping_cost'  => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'status'         => 'nullable|string|in:pending,ordered,received',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => "required|uuid|exists:products,id,tenant_id,$tenantId",
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.tax_id'     => "nullable|uuid|exists:taxes,id,tenant_id,$tenantId",
            'items.*.discount'   => 'nullable|numeric|min:0',
            'items.*.discount_type' => 'nullable|integer|in:0,1',
        ]);

        // Branch must belong to the user's company.
        if (!empty($validated['branch_id']) && ! auth()->user()->canAccessBranch($validated['branch_id'])) {
            return response()->json(['message' => 'Invalid branch.'], 422);
        }

        $purchase = DB::transaction(function () use ($validated, $tenantId) {
            $items = $validated['items'];
            unset($validated['items']);

            $validated['tenant_id'] = $tenantId;
            $validated['purchase_number'] = Purchase::generateNumber();
            $validated['created_by'] = auth()->id();

            if (empty($validated['branch_id'])) {
                $user = auth()->user();
                $validated['branch_id'] = $user->branch_id ?? $tenantId;
            }

            $validated['subtotal'] = 0;
            $validated['tax_total'] = 0;

            $purchase = Purchase::create($validated);

            $itemSubtotal = 0;
            $itemTaxTotal = 0;

            foreach ($items as $item) {
                $product = Product::where('tenant_id', $tenantId)->findOrFail($item['product_id']);
                $tax = isset($item['tax_id']) ? \App\Models\Tax::where('tenant_id', $tenantId)->find($item['tax_id']) : null;

                $lineTotal = bcmul($item['quantity'], $item['unit_cost'], 4);
                $itemDiscount = $item['discount'] ?? 0;
                if ($itemDiscount > 0) {
                    $discountType = $item['discount_type'] ?? 0;
                    $discountAmount = $discountType === 0
                        ? bcmul($lineTotal, bcdiv($itemDiscount, 100, 4), 4)
                        : $itemDiscount;
                    $lineTotal = bcsub($lineTotal, $discountAmount, 4);
                }

                $taxAmount = 0;
                if ($tax) {
                    if ($tax->is_fixed) {
                        $taxAmount = bcmul($tax->rate, $item['quantity'], 4);
                    } else {
                        $taxAmount = bcmul($lineTotal, bcdiv($tax->rate, 100, 4), 4);
                    }
                }

                $purchaseItem = PurchaseItem::create([
                    'purchase_id'      => $purchase->id,
                    'product_id'       => $item['product_id'],
                    'quantity'         => $item['quantity'],
                    'received_quantity' => $validated['status'] === 'received' ? $item['quantity'] : 0,
                    'unit_cost'        => $item['unit_cost'],
                    'tax_id'           => $item['tax_id'] ?? null,
                    'tax_amount'       => $taxAmount,
                    'discount'         => $item['discount'] ?? 0,
                    'discount_type'    => $item['discount_type'] ?? 0,
                    'total'            => bcadd($lineTotal, $taxAmount, 4),
                ]);

                // Auto-receive stock if status is received
                if ($validated['status'] === 'received') {
                    $this->updateStock($purchase, $purchaseItem, $item['quantity']);
                }

                $itemSubtotal = bcadd($itemSubtotal, $lineTotal, 4);
                $itemTaxTotal = bcadd($itemTaxTotal, $taxAmount, 4);
            }

            $purchaseDiscount = $validated['discount'] ?? 0;
            $purchaseDiscountType = $validated['discount_type'] ?? 0;
            $discountAmount = $purchaseDiscountType === 0
                ? bcmul($itemSubtotal, bcdiv($purchaseDiscount, 100, 4), 4)
                : $purchaseDiscount;
            $afterDiscount = bcsub($itemSubtotal, $discountAmount, 4);

            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = bcadd(bcadd($afterDiscount, $itemTaxTotal, 4), $shipping, 4);

            $purchase->update([
                'subtotal'    => $itemSubtotal,
                'discount'    => $purchaseDiscount,
                'discount_type' => $purchaseDiscountType,
                'tax_total'   => $itemTaxTotal,
                'shipping_cost' => $shipping,
                'grand_total' => $grandTotal,
                'due_amount'  => $grandTotal,
            ]);

            if ($validated['status'] === 'received') {
                $purchase->update([
                    'received_date' => now(),
                    'received_by'   => auth()->id(),
                ]);
            }

            return $purchase;
        });

        $purchase->load(['supplier:id,name', 'warehouse:id,name', 'items.product:id,name,code']);
        return response()->json(['data' => $purchase], 201);
    }

    public function show(string $id): JsonResponse
    {
        $purchase = $this->findPurchase($id);
        $purchase->load([
            'supplier:id,name,code,phone_number,email',
            'warehouse:id,name',
            'items.product:id,name,code,cost',
            'items.tax:id,name,rate',
            'createdBy:id,first_name,last_name',
        ]);

        return response()->json(['data' => $purchase]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $purchase = $this->findPurchase($id);

        if (!in_array($purchase->status, ['pending', 'ordered'])) {
            return response()->json(['message' => 'Only pending or ordered purchases can be edited.'], 422);
        }

        $validated = $request->validate([
            'supplier_id'    => 'nullable|uuid|exists:customers,id',
            'warehouse_id'   => 'nullable|uuid|exists:warehouses,id',
            'reference_number' => 'nullable|string|max:100',
            'purchase_date'  => 'sometimes|date',
            'expected_date'  => 'nullable|date',
            'discount'       => 'nullable|numeric|min:0',
            'discount_type'  => 'nullable|integer|in:0,1',
            'shipping_cost'  => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'status'         => 'nullable|string|in:pending,ordered',
        ]);

        $purchase->update($validated);

        return response()->json(['data' => $purchase]);
    }

    public function destroy(string $id): JsonResponse
    {
        $purchase = $this->findPurchase($id);

        if ($purchase->status === 'received') {
            return response()->json(['message' => 'Received purchases cannot be deleted.'], 422);
        }

        $purchase->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Purchase cancelled']);
    }

    public function receive(Request $request, string $id): JsonResponse
    {
        $purchase = $this->findPurchase($id);

        if ($purchase->status === 'received') {
            return response()->json(['message' => 'Already received'], 422);
        }

        if ($purchase->status === 'cancelled') {
            return response()->json(['message' => 'Cancelled purchases cannot be received'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id'  => 'required|uuid|exists:purchase_items,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            $allReceived = true;

            foreach ($validated['items'] as $ri) {
                $item = $purchase->items()->findOrFail($ri['item_id']);
                $qty = min($ri['quantity'], bcsub($item->quantity, $item->received_quantity, 4));
                if ($qty <= 0) continue;

                $item->increment('received_quantity', $qty);
                $this->updateStock($purchase, $item, $qty);

                if (bccomp($item->fresh()->received_quantity, $item->quantity, 4) < 0) {
                    $allReceived = false;
                }
            }

            $status = $allReceived ? 'received' : 'partially_received';
            $purchase->update([
                'status'        => $status,
                'received_date' => $status === 'received' ? now() : null,
                'received_by'   => auth()->id(),
            ]);
        });

        $purchase->load('items');
        return response()->json(['data' => $purchase]);
    }

    public function markPaid(string $id): JsonResponse
    {
        $purchase = $this->findPurchase($id);

        if ($purchase->payment_status === 'paid') {
            return response()->json(['message' => 'Already fully paid'], 422);
        }

        $remaining = round((float) $purchase->due_amount, 4);
        $purchase->update([
            'paid_amount'    => $purchase->grand_total,
            'due_amount'     => 0,
            'payment_status' => 'paid',
        ]);

        // Keep the supplier ledger in sync: the payment list must show the record.
        if ($remaining > 0) {
            \App\Models\PurchasePayment::create([
                'tenant_id'   => $purchase->tenant_id,
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'amount'      => $remaining,
                'date'        => now()->toDateString(),
                'user_id'     => auth()->id(),
                'payment_method' => 'cash',
                'note'        => 'Marked as paid',
            ]);
        }

        return response()->json(['data' => $purchase->load('payments')]);
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json(['number' => Purchase::generateNumber()]);
    }

    private function updateStock(Purchase $purchase, PurchaseItem $item, float|string $qty): void
    {
        $warehouseId = $purchase->warehouse_id;
        if (!$warehouseId) {
            $warehouseId = \App\Models\Warehouse::where('tenant_id', $purchase->tenant_id)
                ->where('is_default', true)
                ->first()?->id;
        }
        if (!$warehouseId) return;

        $stock = Stock::firstOrCreate(
            ['tenant_id' => $purchase->tenant_id, 'product_id' => $item->product_id, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'version' => 0]
        );
        $stock->increment('quantity', $qty);
        $stock->increment('version');

        StockMovement::create([
            'tenant_id'       => $purchase->tenant_id,
            'product_id'      => $item->product_id,
            'warehouse_id'    => $warehouseId,
            'movement_type'   => MovementType::Purchase->value,
            'quantity_change' => $qty,
            'quantity_after'  => $stock->fresh()->quantity,
            'reference_type'  => 'purchase',
            'reference_id'    => $purchase->id,
            'purchase_id'     => $purchase->id,
            'user_id'         => auth()->id(),
        ]);

        if (!SystemModeService::isSingleMode() && $purchase->branch_id) {
            $branchInv = BranchInventory::firstOrCreate(
                ['tenant_id' => $purchase->tenant_id, 'product_id' => $item->product_id, 'branch_id' => $purchase->branch_id],
                ['stock' => 0, 'reserved_stock' => 0]
            );
            $branchInv->updateStock($qty);
        }

        $product = $item->product;
        if ($product) {
            $product->update([
                'last_purchase_price' => $item->unit_cost,
                'cost'               => $item->unit_cost,
            ]);
        }
    }

    public function addPayment(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:255',
        ]);

        $purchase = $this->findPurchase($id);

        if ((float) $purchase->due_amount <= 0) {
            return response()->json(['message' => 'No outstanding balance.'], 422);
        }

        $amount = min(round((float) $validated['amount'], 4), round((float) $purchase->due_amount, 4));
        $newPaid = round((float) $purchase->paid_amount + $amount, 4);
        $newDue = round((float) $purchase->grand_total - $newPaid, 4);
        $newStatus = $newDue <= 0.0001 ? 'paid' : 'partial';

        $purchase->update([
            'paid_amount' => $newPaid,
            'due_amount' => $newDue,
            'payment_status' => $newStatus,
        ]);

        \App\Models\PurchasePayment::create([
            'tenant_id' => $purchase->tenant_id,
            'purchase_id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'payment_method' => $validated['payment_method'] ?? null,
            'note' => $validated['note'] ?? null,
            'date' => now(),
        ]);

        return response()->json([
            'message' => 'Payment recorded.',
            'data' => $purchase->fresh(),
        ]);
    }

    private function findPurchase(string $id): Purchase
    {
        return Purchase::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
    }
}
