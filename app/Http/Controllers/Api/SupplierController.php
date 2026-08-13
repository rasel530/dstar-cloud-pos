<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_supplier', true)
            ->orderBy('name');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone_number', 'ilike', "%{$search}%");
            });
        }

        $suppliers = $query->paginate($request->query('per_page', 25));

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'nullable|string|max:50|unique:customers,code',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'tax_number'   => 'nullable|string|max:100',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'country_id'   => 'nullable|uuid|exists:countries,id',
            'due_date_period' => 'nullable|integer|min:0',
            'is_enabled'   => 'sometimes|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_supplier'] = true;

        if (empty($validated['code'])) {
            $validated['code'] = $this->generateCode(auth()->user()->tenant_id);
        }

        $supplier = Customer::create($validated);

        return response()->json(['data' => $supplier], 201);
    }

    public function show(string $id): JsonResponse
    {
        $supplier = $this->findSupplier($id);

        $supplier->loadCount(['purchases', 'purchaseReturns']);

        return response()->json(['data' => $supplier]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $supplier = $this->findSupplier($id);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'code'         => 'nullable|string|max:50|unique:customers,code,' . $id,
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'tax_number'   => 'nullable|string|max:100',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'country_id'   => 'nullable|uuid|exists:countries,id',
            'due_date_period' => 'nullable|integer|min:0',
            'is_enabled'   => 'sometimes|boolean',
        ]);

        $supplier->update($validated);

        return response()->json(['data' => $supplier]);
    }

    public function destroy(string $id): JsonResponse
    {
        $supplier = $this->findSupplier($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted']);
    }

    public function quickList(): JsonResponse
    {
        $suppliers = Customer::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_supplier', true)
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'phone_number']);

        return response()->json(['data' => $suppliers]);
    }

    private function findSupplier(string $id): Customer
    {
        return Customer::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_supplier', true)
            ->findOrFail($id);
    }

    private function generateCode(string $tenantId): string
    {
        $last = Customer::where('tenant_id', $tenantId)
            ->where('is_supplier', true)
            ->where('code', 'like', 'SUP-%')
            ->orderBy('code', 'desc')
            ->first();

        if ($last && preg_match('/SUP-(\d+)/', $last->code, $m)) {
            $next = (int) $m[1] + 1;
        } else {
            $next = 1;
        }

        return 'SUP-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function statement(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $supplier = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_supplier', true)
            ->findOrFail($id);

        $purchases = \App\Models\Purchase::where('supplier_id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('purchase_date')
            ->get();

        $totalPurchased = round((float) $purchases->sum('grand_total'), 4);
        $totalPaid = round((float) $purchases->sum('paid_amount'), 4);
        $totalDue = round((float) $purchases->sum('due_amount'), 4);

        $paymentRecords = \App\Models\PurchasePayment::whereIn('purchase_id', $purchases->pluck('id'))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'supplier' => $supplier,
                'purchases' => $purchases,
                'payments' => $paymentRecords,
                'summary' => [
                    'total_purchased' => $totalPurchased,
                    'total_paid' => $totalPaid,
                    'total_due' => $totalDue,
                ],
            ],
        ]);
    }

    public function payments(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $supplier = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_supplier', true)
            ->findOrFail($id);

        $payments = \App\Models\PurchasePayment::where('supplier_id', $id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json(['data' => $payments]);
    }
}
