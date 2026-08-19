<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $query = PosOrder::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when(request()->header('X-Active-Branch'), fn($q, $bid) => $q->where('branch_id', $bid))
            ->with([
                "customer",
                "user",
                "posOrderItems.product",
                "document",
            ])
            ->withCount("posOrderItems");

        if ($request->has("status")) {
            $query->where("status", $request->status);
        }

        if ($request->has("customer_id")) {
            $query->where("customer_id", $request->customer_id);
        }

        if ($request->has("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        if ($request->has("date_from")) {
            $query->whereDate("created_at", ">=", $request->date_from);
        }

        if ($request->has("date_to")) {
            $query->whereDate("created_at", "<=", $request->date_to);
        }

        if ($request->filled("q")) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where("number", "ilike", "%{$search}%")
                  ->orWhereHas("customer", function ($cq) use ($search) {
                      $cq->where("name", "ilike", "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy("created_at", "desc")->paginate(25);
        $orders->getCollection()->transform(function ($order) {
            $order->setAttribute('due_amount', round((float) ($order->document->due_amount ?? 0), 2));
            return $order;
        });

        return response()->json(["data" => $orders]);
    }

    public function show($id)
    {
        $o = PosOrder::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('customer')
            ->first();
        if (! $o) {
            return response()->json(["message" => "Order not found"], 404);
        }
        $items = PosOrderItem::with('product')
            ->where('pos_order_id', $id)
            ->get()
            ->each(function ($item) {
                $item->product_name = $item->product?->name ?? 'Item';
                $item->product_price = $item->product?->price ?? 0;
            });
        $o->pos_order_items = $items;
        $o->total = round((float) $o->total, 2);
        $o->discount = round((float) $o->discount, 2);
        return response()->json(["data" => $o]);
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            "items" => "required|array|min:1",
            "items.*.product_id" => "required|exists:products,id,tenant_id,$tenantId",
            "items.*.quantity" => "required|numeric|min:0.01",
            "items.*.price" => "required|numeric|min:0",
            "customer_id" => "nullable|exists:customers,id,tenant_id,$tenantId",
            "service_type" => "nullable|integer|min:0|max:1",
            "table_number" => "nullable|string|max:50",
        ]);

        $user = auth()->user();
        $branchId = $request->header('X-Active-Branch');
        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $branchId = $user->branch_id ?? $user->tenant_id;
        }
        $branchId = $branchId ?: ($user->branch_id ?? $user->tenant_id);

        $canEditPrice = $user->can_edit_price || $user->access_level >= 5;

        foreach ($request->items as $item) {
            $product = \App\Models\Product::where('tenant_id', $tenantId)
                ->orWhere('is_global', true)
                ->find($item["product_id"]);
            if (! $product) {
                return response()->json(['message' => 'Product not found.'], 422);
            }
            if (! $canEditPrice) {
                $price = (float) $item['price'];
                if ($product->price > 0 && $price < $product->price) {
                    return response()->json(['message' => "Price cannot be below the catalog price for '{$product->name}'."], 422);
                }
                if ($price > $product->price * 5) {
                    return response()->json(['message' => 'Price exceeds maximum allowed (5x product price).'], 422);
                }
            }
        }

        $branch = \App\Models\Tenant::find($branchId);
        $branchCode = (!\App\Services\SystemModeService::isSingleMode() && $branch?->branch_code) ? $branch->branch_code . '-' : '';
        $dateFmt = $branchCode . "ORD-" . now()->format("Ymd") . "-";
        $count = PosOrder::where('tenant_id', $tenantId)
            ->whereDate("created_at", today())
            ->where('number', 'like', $dateFmt . '%')
            ->count() + 1;
        $orderNumber = $dateFmt . str_pad($count, 4, "0", STR_PAD_LEFT);

        $order = PosOrder::create([
            "tenant_id"     => $tenantId,
            "user_id"       => $user->id,
            "customer_id"   => $request->customer_id,
            "branch_id"     => $branchId,
            "number"        => $orderNumber,
            "service_type"  => $request->service_type ?? 0,
            "table_number"  => $request->table_number ?? null,
            "status"        => "open",
            "total"         => $request->total ?? 0,
            "tax_amount"    => $request->tax_amount ?? 0,
            "discount"      => $request->discount ?? 0,
            "discount_type" => $request->discount_type ?? 0,
        ]);

        foreach ($request->items as $item) {
            $product = \App\Models\Product::where('tenant_id', $tenantId)
                ->orWhere('is_global', true)
                ->find($item["product_id"]);
            PosOrderItem::create([
                "pos_order_id" => $order->id,
                "product_id"   => $item["product_id"],
                "quantity"     => $item["quantity"],
                "price"        => $item["price"],
                "cost"         => $product?->cost ?? 0,
            ]);
        }

        return response()->json(["data" => ["id" => $order->id]], 201);
    }

    public function closeOrder(Request $request, $order)
    {
        $o = PosOrder::where('id', $order)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();
        if (! $o) return response()->json(["message" => "Order not found"], 404);
        if ($o->status === "closed") return response()->json(["message" => "Order is already closed"], 422);

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        try {
            $doc = \Illuminate\Support\Facades\DB::transaction(function () use ($o, $order, $user, $tenantId) {
                // Atomic: only one process may close the order and write its records.
                $locked = PosOrder::where('id', $order)->lockForUpdate()->first();
                if (! $locked || $locked->status === 'closed') {
                    throw new \Illuminate\Validation\ValidationException(validator([], ['order' => 'Order is already closed.']));
                }

                $orderModel = PosOrder::with('posOrderItems.product')->find($order);
                $calc = (new \App\Services\Pricing\TaxCalculator)->calculate($orderModel);
                $finalTotal = $calc['total'];

                $roundingRule = (string) (\App\Models\ApplicationSetting::where('tenant_id', $tenantId)->where('key', 'rounding_rule')->value('value') ?? 'none');
                $finalTotal = $this->applyRoundingRule((float) $finalTotal, $roundingRule);

                $branch = $o->branch_id ? \App\Models\Tenant::find($o->branch_id) : null;
                $branchCode = (!\App\Services\SystemModeService::isSingleMode() && $branch?->branch_code) ? $branch->branch_code . '-' : '';
                $dateFmt = $branchCode . "ORD-" . now()->format("Ymd") . "-";
                $count = Document::where('tenant_id', $tenantId)
                    ->whereDate("created_at", today())
                    ->where('number', 'like', $dateFmt . '%')
                    ->count() + 1;
                $docNumber = $dateFmt . str_pad($count, 4, "0", STR_PAD_LEFT);

                $docType = \App\Models\DocumentType::where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })->where("code", "200")->first();
                $warehouse = \App\Models\Warehouse::where("tenant_id", $tenantId)->first();
                $paymentType = \App\Models\PaymentType::where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })->where('code', 'cash')->first()
                    ?? \App\Models\PaymentType::where(function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                    })->first();

                $doc = Document::create([
                    "tenant_id" => $tenantId,
                    "user_id" => $user->id,
                    "customer_id" => $o->customer_id,
                    "number" => $docNumber,
                    "order_number" => $o->number,
                    "document_type_id" => $docType?->id,
                    "warehouse_id" => $warehouse?->id,
                    "date" => now()->toDateString(),
                    "stock_date" => now(),
                    "total" => $finalTotal,
                    "paid_amount" => $finalTotal,
                    "due_amount" => 0,
                    "tax_amount" => $calc['tax'] ?? 0,
                    "discount" => $calc['discount'] ?? 0,
                    "discount_type" => 0,
                    "paid_status" => 1,
                ]);

                foreach ($orderModel->posOrderItems as $oi) {
                    DocumentItem::create([
                        "document_id" => $doc->id,
                        "product_id" => $oi->product_id,
                        "quantity" => $oi->quantity,
                        "price" => $oi->price,
                        "total" => $oi->quantity * $oi->price,
                    ]);
                }

                if ($finalTotal > 0) {
                    Payment::create([
                        "tenant_id" => $tenantId,
                        "document_id" => $doc->id,
                        "payment_type_id" => $paymentType?->id,
                        "user_id" => $user->id,
                        "amount" => $finalTotal,
                    ]);
                }

                \DB::table('pos_orders')->where('id', $order)->where('status', '!=', 'closed')->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'total' => $finalTotal,
                    'discount' => $calc['discount'] ?? 0,
                    'paid_amount' => $finalTotal,
                    'payment_method' => $paymentType?->code ?? 'cash',
                    'tax_amount' => $calc['tax'] ?? 0,
                    'updated_at' => now(),
                ]);

                // Stock decrement (warehouse + branch inventory), same as checkout.
                $warehouseId = $warehouse?->id;
                $allowNegative = \App\Models\ApplicationSetting::where('tenant_id', $tenantId)->where('key', 'allow_negative_stock')->value('value');
                $allowNegative = $allowNegative === 'true' || $allowNegative === '1';
                $branchId = $o->branch_id;
                if ($warehouseId && $user->canAccessBranch($branchId ?? $tenantId)) {
                    $stockService = new \App\Services\Inventory\StockService;
                    foreach ($orderModel->posOrderItems as $item) {
                        $product = \App\Models\Product::find($item->product_id);
                        if (!$product || $product->is_service || !$product->track_inventory) continue;
                        $qty = (float) ($item->quantity ?? 1);
                        try {
                            if ($branchId) {
                                $bi = \App\Models\BranchInventory::where('product_id', $item->product_id)->where('branch_id', $branchId)->first();
                                $current = $bi ? (float) $bi->stock : 0;
                                if (!$allowNegative && $current < $qty) {
                                    throw new \RuntimeException("Insufficient stock for '{$product->name}'. Available: {$current}");
                                }
                            }
                            $stockService->decrement($item->product_id, $warehouseId, $qty, $user->id, $tenantId, 'sale', $order);
                            if ($branchId) {
                                $bi = \App\Models\BranchInventory::where('product_id', $item->product_id)->where('branch_id', $branchId)->first();
                                if ($bi) { $bi->updateStock(-$qty); }
                                else { \App\Models\BranchInventory::create(['tenant_id' => $tenantId, 'product_id' => $item->product_id, 'branch_id' => $branchId, 'stock' => -$qty]); }
                            }
                        } catch (\RuntimeException $e) {
                            if (!$allowNegative) { throw $e; }
                        }
                    }
                }

                return $doc;
            });

            try {
                $orderModel = PosOrder::with('customer')->find($order);
                if ($orderModel) {
                    (new \App\Services\LoyaltyAutoAwardService)->awardForOrder($orderModel);
                }
            } catch (\Exception $e) { /* loyalty award is non-critical */ }

            return response()->json(["data" => $o->refresh()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(["message" => "Order is already closed"], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('closeOrder failed: ' . $e->getMessage(), ['order_id' => $order]);
            return response()->json(["message" => "Failed to close order. Please try again."], 500);
        }
    }

    public function holdOrders(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = PosOrder::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['open', 'held'])
            ->when($request->header('X-Active-Branch'), fn($q, $bid) => $q->where('branch_id', $bid))
            ->with(['customer:id,name', 'user:id,first_name,last_name', 'posOrderItems.product'])
            ->withCount('posOrderItems')
            ->orderByDesc('updated_at');

        return response()->json(['data' => $query->get()]);
    }

    public function holdOrder(Request $request, $order): \Illuminate\Http\JsonResponse
    {
        $o = PosOrder::where('id', $order)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();
        if (!$o) return response()->json(['message' => 'Order not found'], 404);
        if (!in_array($o->status, ['open'])) {
            return response()->json(['message' => 'Only open orders can be held.'], 422);
        }
        $o->update(['status' => 'held', 'held_at' => now()]);
        return response()->json(['data' => $o->fresh()]);
    }

    public function resumeOrder(Request $request, $order): \Illuminate\Http\JsonResponse
    {
        $o = PosOrder::where('id', $order)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();
        if (!$o) return response()->json(['message' => 'Order not found'], 404);
        if (!in_array($o->status, ['held', 'open'])) {
            return response()->json(['message' => 'Only held or open orders can be resumed.'], 422);
        }
        $o->update(['status' => 'open', 'held_at' => null]);
        return response()->json(['data' => $o->fresh()]);
    }

    public function cancelOrder(Request $request, $order): \Illuminate\Http\JsonResponse
    {
        $o = PosOrder::where('id', $order)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();
        if (!$o) return response()->json(['message' => 'Order not found'], 404);
        if (!in_array($o->status, ['open', 'held'])) {
            return response()->json(['message' => 'Only open/held orders can be cancelled.'], 422);
        }
        $o->update(['status' => 'cancelled', 'closed_at' => now()]);
        return response()->json(['data' => $o->fresh()]);
    }

    public function addItem(Request $request, $orderId)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'product_id' => "required|exists:products,id,tenant_id,$tenantId",
            'quantity'   => 'required|numeric|min:0.01|max:9999',
            'price'      => 'required|numeric|min:0',
        ]);

        $order = PosOrder::where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        if ($order->status !== 'open') {
            return response()->json(['message' => 'Only open orders can be modified.'], 422);
        }

        $product = \App\Models\Product::where('tenant_id', $tenantId)
            ->orWhere('is_global', true)
            ->findOrFail($validated['product_id']);
        if ($validated['price'] > ($product->price * 5)) {
            return response()->json(['message' => 'Price exceeds maximum allowed (5x product price).'], 422);
        }
        // Cashiers without price-edit permission cannot undercharge below catalog price.
        $canEditPrice = auth()->user()->can_edit_price || auth()->user()->access_level >= 5;
        if (! $canEditPrice && $product->price > 0 && (float) $validated['price'] < (float) $product->price) {
            return response()->json(['message' => "Price cannot be below the catalog price for '{$product->name}'."], 422);
        }

        $item = PosOrderItem::create([
            "pos_order_id" => $orderId,
            "product_id"   => $validated['product_id'],
            "quantity"     => $validated['quantity'],
            "price"        => $validated['price'],
            "cost"         => $product->cost ?? 0,
            "round_number" => 1,
        ]);
        return response()->json(["data" => $item], 201);
    }

    public function removeItem(Request $request, $orderId, $itemId)
    {
        $order = PosOrder::where('id', $orderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'open') {
            return response()->json(['message' => 'Only open orders can be modified.'], 422);
        }

        $item = PosOrderItem::where("pos_order_id", $orderId)->where("id", $itemId)->firstOrFail();
        $item->delete();
        return response()->json(null, 204);
    }

    public function checkout(Request $request, $orderId)
    {
        $order = PosOrder::with("posOrderItems.product")
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($orderId);
        $user = auth()->user();

        $calculatedTotal = $order->posOrderItems->sum(fn($i) => $i->quantity * $i->price);
        $discount = $request->input('discount', $order->discount ?? 0);
        $discountType = $request->input('discount_type', $order->discount_type ?? 0);
        $discountAmount = round(floatval($discount), 2);
        // Promotions are a separate flat discount so the 50% guard below only
        // applies to the manual discount, never blocking admin-configured promos.
        $promoDiscount = round(max(0, floatval($request->input('promo_discount', 0))), 2);

if ($discountAmount > $calculatedTotal * 0.5) {
return response()->json(['message' => 'Discount cannot exceed 50% of order total.'], 422);
}

        $order->discount = $discountAmount + $promoDiscount;
        $order->discount_type = $discountType;
        $calc = (new \App\Services\Pricing\TaxCalculator)->calculate($order);
        $finalTotal = $calc['total'];

        $tenantId = $user->tenant_id;

        // Apply the same rounding rule as the POS frontend so the charged total,
        // change and amount due always match what the cashier sees on screen.
        $roundingRule = (string) (\App\Models\ApplicationSetting::where('tenant_id', $tenantId)->where('key', 'rounding_rule')->value('value') ?? 'none');
        $finalTotal = $this->applyRoundingRule((float) $finalTotal, $roundingRule);

        $branchId = $request->header('X-Active-Branch') ?: $order->branch_id;
        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $branchId = $user->branch_id ?? $user->tenant_id;
        }
        $paymentMethod = $request->input('payment_type', 'cash');
        $paidAmount = (float) ($request->input('paid_amount') ?: $finalTotal);
        $changeAmount = max(0, $paidAmount - $finalTotal);
        $taxAmount = $calc['tax'] ?? 0;

        $branch = $branchId ? \App\Models\Tenant::find($branchId) : null;
        $branchCode = (!\App\Services\SystemModeService::isSingleMode() && $branch?->branch_code) ? $branch->branch_code . '-' : '';
        $dateFmt = $branchCode . "ORD-" . now()->format("Ymd") . "-";
        $count = Document::where('tenant_id', $tenantId)
            ->whereDate("created_at", today())
            ->where('number', 'like', $dateFmt . '%')
            ->count() + 1;
        $docNumber = $dateFmt . str_pad($count, 4, "0", STR_PAD_LEFT);
        $docType = \App\Models\DocumentType::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->where("code", "200")->first();
        $warehouse = \App\Models\Warehouse::where("tenant_id", $tenantId)->first();
        $paymentType = \App\Models\PaymentType::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->where(function ($q) use ($paymentMethod) {
            $q->where('code', $paymentMethod)->orWhere('name', 'ilike', $paymentMethod);
        })->first() ?? \App\Models\PaymentType::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->first();

        $openRegister = \App\Models\CashRegister::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'open')
            ->when($branchId && !\App\Services\SystemModeService::isSingleMode(),
                fn($q) => $q->where('branch_id', $branchId),
                fn($q) => $q->whereNull('branch_id'))
            ->latest('opened_at')->first();

        // Optional policy: block sales until the cashier opens a register.
        $requireRegister = \App\Models\ApplicationSetting::where('tenant_id', $tenantId)
            ->where('key', 'require_cash_register')
            ->value('value');
        $requireRegister = $requireRegister === 'true' || $requireRegister === '1';
        if ($requireRegister && ! $openRegister) {
            return response()->json(['message' => 'Please open the cash register before completing this sale.'], 422);
        }

        $isCredit = $paymentType && !$paymentType->mark_as_paid;
        $effectiveCustomerId = $request->input('customer_id', $order->customer_id);
        if ($isCredit && !$effectiveCustomerId) {
            return response()->json(['message' => 'Customer Due (credit) requires a registered customer. Please select a customer first.'], 422);
        }
        $appliedPaid = $isCredit ? 0 : min($paidAmount, $finalTotal);
        $dueAmount = $isCredit ? $finalTotal : max(0, $finalTotal - $appliedPaid);
        $docPaidAmount = $appliedPaid;
        $docDueAmount = $dueAmount;
        $docPaidStatus = $isCredit ? 0 : ($dueAmount > 0.0001 ? 2 : 1);
        $paymentAmount = $appliedPaid;

        $warehouseId = $request->input('warehouse_id') ?: \App\Models\Warehouse::where('tenant_id', $tenantId)->where('is_default', true)->value('id');
        $allowNegative = \App\Models\ApplicationSetting::where('tenant_id', $tenantId)->where('key', 'allow_negative_stock')->value('value');
        $allowNegative = $allowNegative === 'true' || $allowNegative === '1';
        $stockBranchId = $request->input('branch_id') ?: $branchId;
        if ($stockBranchId && ! $user->canAccessBranch($stockBranchId)) {
            $stockBranchId = null;
        }

        try {
            $doc = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $orderId, $order, $user, $tenantId, $docNumber, $docType, $warehouse, $finalTotal, $taxAmount, $discountAmount, $discountType, $docPaidAmount, $docDueAmount, $docPaidStatus, $effectiveCustomerId, $paymentAmount, $paymentType, $openRegister, $stockBranchId, $warehouseId, $allowNegative, $paidAmount, $changeAmount, $paymentMethod) {
                // Atomic replay protection: lock the order row and require it to still be open.
                $locked = PosOrder::where('id', $orderId)->lockForUpdate()->first();
                if (! $locked || $locked->status !== 'open') {
                    throw new \Illuminate\Validation\ValidationException(validator([], ['order' => 'This order has already been processed.']));
                }

                $doc = Document::create([
                    "tenant_id" => $user->tenant_id,
                    "user_id" => $user->id,
                    "customer_id" => $effectiveCustomerId,
                    "number" => $docNumber,
                    "order_number" => $order->number,
                    "document_type_id" => $docType?->id,
                    "warehouse_id" => $warehouse?->id,
                    "date" => now()->toDateString(),
                    "stock_date" => now(),
                    "total" => $finalTotal,
                    "paid_amount" => $docPaidAmount,
                    "due_amount" => $docDueAmount,
                    "tax_amount" => $taxAmount,
                    "discount" => $discountAmount,
                    "discount_type" => $discountType,
                    "paid_status" => $docPaidStatus,
                ]);

                foreach ($order->posOrderItems as $oi) {
                    DocumentItem::create([
                        "document_id" => $doc->id,
                        "product_id" => $oi->product_id,
                        "quantity" => $oi->quantity,
                        "price" => $oi->price,
                        "total" => $oi->quantity * $oi->price,
                    ]);
                }

                Payment::create([
                    "tenant_id" => $user->tenant_id,
                    "document_id" => $doc->id,
                    "payment_type_id" => $paymentType?->id,
                    "user_id" => $user->id,
                    "amount" => $paymentAmount,
                    "cash_register_id" => $openRegister?->id,
                ]);

                if ($openRegister) {
                    $openRegister->update(['last_activity_at' => now()]);
                }

                $closed = \DB::table('pos_orders')->where('id', $orderId)->where('status', 'open')->update([
                    'status' => 'closed',
                    'total' => $finalTotal,
                    'discount' => $discountAmount,
                    'discount_type' => $discountType,
                    'paid_amount' => $docPaidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'tax_amount' => $taxAmount,
                    'customer_id' => $effectiveCustomerId,
                    'branch_id' => $stockBranchId ?? $order->branch_id,
                    'updated_at' => now(),
                ]);

                if (! $closed) {
                    throw new \Illuminate\Validation\ValidationException(validator([], ['order' => 'This order has already been processed.']));
                }

                if ($warehouseId) {
                    $stockService = new \App\Services\Inventory\StockService;
                    foreach ($order->posOrderItems as $item) {
                        $product = \App\Models\Product::find($item->product_id);
                        if (!$product || $product->is_service || !$product->track_inventory) continue;
                        $qty = (float) ($item->quantity ?? 1);
                        try {
                            if ($stockBranchId) {
                                $bi = \App\Models\BranchInventory::where('product_id', $item->product_id)->where('branch_id', $stockBranchId)->first();
                                $current = $bi ? (float) $bi->stock : 0;
                                if (!$allowNegative && $current < $qty) {
                                    throw new \RuntimeException("Insufficient stock for '{$product->name}'. Available: {$current}");
                                }
                            }
                            $stockService->decrement($item->product_id, $warehouseId, $qty, $user->id, $user->tenant_id, 'sale', $orderId);

                            if ($stockBranchId) {
                                $bi = \App\Models\BranchInventory::where('product_id', $item->product_id)->where('branch_id', $stockBranchId)->first();
                                if ($bi) { $bi->updateStock(-$qty); }
                                else { \App\Models\BranchInventory::create(['tenant_id' => $user->tenant_id, 'product_id' => $item->product_id, 'branch_id' => $stockBranchId, 'stock' => -$qty]); }
                            }
                        } catch (\RuntimeException $e) {
                            if (!$allowNegative) { throw $e; }
                        }
                    }
                }

                return $doc;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'This order has already been processed.'], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Checkout failed: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['message' => 'Checkout failed. Please try again.'], 500);
        }

        $order->status = 'closed';
        $order->total = $finalTotal;
        $order->paid_amount = $paidAmount;
        $order->change_amount = $changeAmount;
        $order->payment_method = $paymentMethod;
        $order->tax_amount = $taxAmount;

        try {
            (new \App\Services\LoyaltyAutoAwardService)->awardForOrder($order);
        } catch (\Exception $e) { /* loyalty award is non-critical */ }

        $receipt = $this->buildReceipt($order, $doc);

        // Auto-print to physical thermal printer AFTER the client gets the receipt (non-blocking)
        app()->terminating(function () use ($order, $receipt) {
            try {
                // Flush the response to the client BEFORE the (possibly slow/unreachable)
                // thermal print call, so payment never waits on the print proxy.
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                $this->dispatchAutoPrint($order, $receipt);
            } catch (\Exception $e) { /* printing is non-critical */ }
        });

        // The receipt modal only renders receipt_html; the PDF button re-fetches
        // /api/receipts/{id} on demand, so skip the heavy PDF/text/logo payloads here.
        $modalReceipt = $receipt;
        unset($modalReceipt['pdf_html'], $modalReceipt['receipt_text'], $modalReceipt['logo'], $modalReceipt['receipt_logo']);

        return response()->json(["data" => [
            "receipt" => $modalReceipt,
        ]]);
    }

    public function transferItems(Request $request, $orderId){}
    public function mergeCartItems(Request $request) {}

    public function refund(Request $request, $orderId): JsonResponse
    {
        $user = auth()->user();
        $order = PosOrder::where('id', $orderId)
            ->where('tenant_id', $user->tenant_id)
            ->with('posOrderItems.product')
            ->firstOrFail();

        if ($order->status === 'refunded') {
            return response()->json(['message' => 'Order has already been refunded.', 'data' => $order]);
        }

        if ($order->status !== 'closed') {
            return response()->json(['message' => 'Only completed orders can be refunded.'], 422);
        }

        $reason = $request->input('reason', 'Customer refund');

        $doc = Document::where('order_number', $order->number)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (! $doc) {
            return response()->json(['message' => 'No sale document found for this order.'], 422);
        }

        $alreadyRefunded = false;
        try {
            $checkoutService = app(\App\Services\Pos\CheckoutService::class);
            $refundResult = $checkoutService->processRefund($doc->id, $user->id, $reason);

            $openRegister = \App\Models\CashRegister::where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('status', 'open')
                ->when($order->branch_id && !\App\Services\SystemModeService::isSingleMode(),
                    fn($q) => $q->where('branch_id', $order->branch_id),
                    fn($q) => $q->whereNull('branch_id'))
                ->latest('opened_at')->first();
            if ($openRegister && isset($refundResult['document'])) {
                \App\Models\Payment::where('document_id', $refundResult['document']->id)
                    ->update(['cash_register_id' => $openRegister->id]);
            }
        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'already been fully refunded')) {
                $alreadyRefunded = true;
            } else {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        } catch (\RuntimeException $e) {
            \Illuminate\Support\Facades\Log::error('Refund processing failed: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['message' => 'Refund failed. Please try again.'], 500);
        }

        if (! $alreadyRefunded) {
            $branchId = $order->branch_id;
            if ($branchId) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($order, $branchId, $user) {
                    foreach ($order->posOrderItems as $item) {
                        $product = $item->product;
                        if (!$product || $product->is_service || !$product->track_inventory) continue;
                        $qty = (float) ($item->quantity ?? 1);

                        $bi = \App\Models\BranchInventory::where('product_id', $item->product_id)->where('branch_id', $branchId)->first();
                        if ($bi) {
                            $bi->updateStock($qty);
                        } else {
                            \App\Models\BranchInventory::create([
                                'tenant_id' => $user->tenant_id,
                                'product_id' => $item->product_id,
                                'branch_id' => $branchId,
                                'stock' => $qty,
                            ]);
                        }
                    }
                });
            }
        }

        $order->update(['status' => 'refunded', 'updated_at' => now()]);

        // Reverse loyalty points earned on the refunded order so balances
        // never retain points for purchases that were returned.
        if (! $alreadyRefunded && (float) $order->loyalty_points_earned > 0 && $order->customer_id) {
            try {
                $card = \App\Models\LoyaltyCard::where('customer_id', $order->customer_id)
                    ->where('tenant_id', $user->tenant_id)
                    ->first();
                if ($card) {
                    $points = (int) $order->loyalty_points_earned;
                    $card->points_balance = max(0, $card->points_balance - $points);
                    $card->total_points_earned = max(0, $card->total_points_earned - $points);
                    $card->save();
                    \App\Models\LoyaltyTransaction::create([
                        'loyalty_card_id' => $card->id,
                        'transaction_type' => 'redeem',
                        'points' => $points,
                        'reference_type' => 'order_refund',
                        'reference_id' => $order->id,
                    ]);
                    \DB::table('pos_orders')->where('id', $order->id)->update(['loyalty_points_earned' => 0]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Loyalty reversal failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            }
        }

        return response()->json(['message' => 'Order refunded successfully.', 'data' => $order]);
    }

    public function voidItem(Request $request, $orderId, $itemId): JsonResponse
    {
        if (!auth()->user() || auth()->user()->access_level < 5) {
            return response()->json(['message' => 'Insufficient access level.'], 403);
        }

        $item = PosOrderItem::where('id', $itemId)
            ->where('pos_order_id', $orderId)
            ->firstOrFail();

        $order = PosOrder::where('id', $orderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if ($order->status === 'closed') {
            return response()->json(['message' => 'Cannot void items on a closed order.'], 422);
        }

        $item->delete();

        return response()->json(['message' => 'Item voided successfully.']);
    }

    public function receipt($orderId): JsonResponse
    {
        $order = PosOrder::where('id', $orderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when(request()->header('X-Active-Branch'), fn($q, $bid) => $q->where('branch_id', $bid))
            ->with(['posOrderItems.product', 'customer', 'user', 'branch'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $doc = Document::where('order_number', $order->number)->first();
        if (!$doc) {
            return response()->json(['message' => 'Receipt not available'], 404);
        }

        $receipt = $this->buildReceipt($order, $doc);
        return response()->json(['data' => $receipt]);
    }

    private function buildReceipt(PosOrder $order, Document $doc): array
    {
        $order->load(['posOrderItems.product', 'customer', 'user', 'branch']);
        $tenantId = $order->tenant_id;

        $settings = \App\Models\ApplicationSetting::where('tenant_id', $tenantId)
            ->orderBy('key')->get()->pluck('value', 'key')->toArray();

        $company = [
            'name' => $settings['company_name'] ?? config('app.name'),
            'address' => $settings['company_address'] ?? '',
            'phone' => $settings['company_phone'] ?? '',
            'email' => $settings['company_email'] ?? '',
        ];

        $branchData = null;
        if (!\App\Services\SystemModeService::isSingleMode() && $order->branch_id && $order->branch) {
            $branchData = [
                'name' => $order->branch->name,
                'branch_code' => $order->branch->branch_code,
                'address' => $order->branch->address ?? '',
                'phone' => $order->branch->phone ?? '',
            ];
        }

        $customerName = $order->customer ? ($order->customer->name ?? '') : '';
        $customerPhone = $order->customer ? ($order->customer->phone_number ?? ($order->customer->phone ?? '')) : '';
        $cashierName = $order->user ? trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? '')) : '';

        $items = $order->posOrderItems->map(fn($i) => [
            'product_name' => $i->product_name ?? $i->product?->name ?? 'Item',
            'quantity' => (float) ($i->quantity ?? 1),
            'price' => (float) ($i->price ?? 0),
            'total' => (float) ($i->quantity ?? 1) * (float) ($i->price ?? 0),
        ])->toArray();

        $receiptNumber = $doc->number;

        $receiptOrder = [
            'number' => $doc->number,
            'created_at' => $order->created_at,
            'date' => $order->created_at->format('Y-m-d H:i:s'),
            'items' => $items,
            'subtotal' => round($order->posOrderItems->sum(fn($i) => $i->quantity * $i->price), 2),
            'tax_amount' => round($order->tax_amount ?? 0, 2),
            'discount' => round($order->discount ?? 0, 2),
            'total' => round($order->total ?? 0, 2),
            'paid_amount' => round((float) ($doc->paid_amount ?? 0), 2),
            'due_amount' => round((float) ($doc->due_amount ?? 0), 2),
            'change_amount' => round($order->change_amount ?? 0, 2),
            'payment_method' => $order->payment_method ?? 'cash',
            'cashier' => $cashierName,
            'customer' => $customerName ?: 'Walk-in Customer',
            'customer_phone' => $customerPhone,
            'branch' => $branchData,
            'document_type' => ['name' => 'Sale'],
            'payments' => [],
            'service_type' => (int) ($order->service_type ?? 0),
            'table_number' => $order->table_number ?? '',
            'order_status' => $order->status,
        ];

        return [
            'receipt_number' => $receiptNumber,
            'order_number' => $order->number,
            'date' => $order->created_at->format('d M Y'),
            'time' => $order->created_at->format('h:i A'),
            'company' => $company,
            'branch' => $branchData,
            'cashier' => $cashierName,
            'customer' => $customerName ?: 'Walk-in Customer',
            'customer_phone' => $customerPhone,
            'items' => $items,
            'subtotal' => $receiptOrder['subtotal'],
            'tax_amount' => $receiptOrder['tax_amount'],
            'discount' => $receiptOrder['discount'],
            'grand_total' => $receiptOrder['total'],
            'paid_amount' => $receiptOrder['paid_amount'],
            'due_amount' => $receiptOrder['due_amount'],
            'change_amount' => $receiptOrder['change_amount'],
            'payment_method' => $receiptOrder['payment_method'],
            'service_type' => (int) ($order->service_type ?? 0),
            'table_number' => $order->table_number ?? '',
            'currency_symbol' => $settings['currency_symbol'] ?? '$',
            'receipt_header' => $settings['receipt_header'] ?? '',
            'receipt_footer' => $settings['receipt_footer'] ?? 'Thank you for your purchase!',
            'logo' => $settings['logo'] ?? '',
            'receipt_logo' => $settings['receipt_logo'] ?? '',
            'order_status' => $order->status,
            'receipt_html' => (new \App\Services\Printing\ReceiptBuilder)->build($receiptOrder, $company, $settings),
            'pdf_html' => (new \App\Services\Printing\ReceiptBuilder)->buildPdf($receiptOrder, $company, $settings),
            'receipt_text' => (new \App\Services\Printing\ReceiptBuilder)->buildText($receiptOrder, $company, $settings),
        ];
    }

    private function applyRoundingRule(float $amount, string $rule): float
    {
        switch ($rule) {
            case 'nearest_001': return round($amount * 100) / 100;
            case 'nearest_005': return round($amount * 20) / 20;
            case 'nearest_010': return round($amount * 10) / 10;
            case 'nearest_050': return round($amount * 2) / 2;
            case 'nearest_1': return round($amount);
            default: return $amount;
        }
    }

    /**
     * Attempt to auto-print the receipt to the configured thermal printer.
     * Never throws — print failure is non-critical; the order still succeeds.
     */
    private function dispatchAutoPrint(PosOrder $order, array $receipt): void
    {
        try {
            $printer = \App\Models\PosPrinterSetting::where('tenant_id', $order->tenant_id)->first();
            if (!$printer || empty($printer->printer_name)) return;

            $receiptText = $receipt['receipt_text'] ?? '';
            if (empty($receiptText)) return;

            $dispatcher = new \App\Services\Printing\PrintJobDispatcher;
            $dispatcher->dispatch($printer->printer_name, $receiptText, $order->tenant_id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Auto-print failed for order '.$order->number, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

