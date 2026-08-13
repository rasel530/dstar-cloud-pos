<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Customer::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->select('customers.*')
            ->selectRaw('COALESCE((SELECT SUM(points_balance) FROM loyalty_cards WHERE customer_id = customers.id), 0) as loyalty_points')
            ->selectRaw('COALESCE((SELECT SUM(due_amount) FROM documents WHERE customer_id = customers.id), 0) as outstanding_balance');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('phone_number', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        return response()->json(['data' => $query->orderBy('created_at', 'desc')->paginate(25)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|max:255',
            'email'         => 'nullable|email',
            'phone_number'  => 'nullable|string',
            'code'          => 'nullable|string',
            'address'       => 'nullable|string',
            'tax_number'    => 'nullable|string',
            'is_enabled'    => 'boolean',
        ]);

        try {
            $data['tenant_id'] = auth()->user()->tenant_id;
            if (empty($data['code'])) {
                $data['code'] = 'CUST-' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT);
            }
            $customer = Customer::create($data);

            \App\Models\LoyaltyCard::firstOrCreate(
                ['customer_id' => $customer->id],
                [
                    'tenant_id' => auth()->user()->tenant_id,
                    'card_number' => 'LC-' . str_pad(\App\Models\LoyaltyCard::count() + 1, 4, '0', STR_PAD_LEFT),
                    'points_balance' => 0,
                    'total_points_earned' => 0,
                ]
            );

            return response()->json(['data' => $customer], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create customer'], 500);
        }
    }    public function quickStore(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|min:7',
            'name'         => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $existing = Customer::where('tenant_id', $user->tenant_id)
            ->where('phone_number', $request->phone_number)
            ->first();

        if ($existing) {
            return response()->json(['data' => $existing, 'existing' => true]);
        }

        $name = $request->name ?: ('Customer ' . $request->phone_number);

        $customer = Customer::create([
            'tenant_id'    => $user->tenant_id,
            'name'         => $name,
            'phone_number' => $request->phone_number,
            'code'         => 'CUST-' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT),
            'is_enabled'   => true,
        ]);

        \App\Models\LoyaltyCard::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'tenant_id'          => $user->tenant_id,
                'card_number'        => 'LC-' . str_pad(\App\Models\LoyaltyCard::count() + 1, 4, '0', STR_PAD_LEFT),
                'points_balance'     => 0,
                'total_points_earned'=> 0,
            ]
        );

        return response()->json(['data' => $customer], 201);
    }

    public function show($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json(['data' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'name'         => 'sometimes|required|max:255',
            'email'        => 'nullable|email',
            'phone_number' => 'nullable|string',
            'code'         => 'nullable|string',
            'is_enabled'   => 'boolean',
        ]);

        try {
            $customer->update($validated);

            return response()->json(['data' => $customer], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Customer update: ' . $e->getMessage(), ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update customer', 'error' => $e->getMessage()], 500);
        }
    }    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        try {
            $customer->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete customer'], 500);
        }
    }

    public function addPayment(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $customer = Customer::where(function ($q) { $q->where('tenant_id', auth()->user()->tenant_id)->orWhereNull('tenant_id'); })->findOrFail($id);

        $docs = \App\Models\Document::where('customer_id', $id)
            ->where('due_amount', '>', 0)
            ->orderBy('date')
            ->get();

        if ($docs->isEmpty()) {
            return response()->json(['message' => 'No outstanding invoices for this customer.'], 422);
        }

        $totalDue = round((float) $docs->sum('due_amount'), 4);
        $amount = min(round((float) $validated['amount'], 4), $totalDue);
        $excess = round((float) $validated['amount'] - $amount, 4);

        $remaining = $amount;
        $allocated = 0;
        $paymentType = \App\Models\PaymentType::where('code', $validated['payment_method'] ?? 'cash')->first()
            ?? \App\Models\PaymentType::first();

        foreach ($docs as $doc) {
            if ($remaining <= 0.0001) break;
            $pay = min(round($remaining, 4), round((float) $doc->due_amount, 4));
            $doc->paid_amount = round((float) $doc->paid_amount + $pay, 4);
            $doc->due_amount = round((float) $doc->due_amount - $pay, 4);
            $doc->paid_status = $doc->due_amount <= 0.0001 ? 1 : 2;
            $doc->save();
            $remaining = round($remaining - $pay, 4);
            $allocated = round($allocated + $pay, 4);

            \App\Models\Payment::create([
                'tenant_id' => $doc->tenant_id,
                'document_id' => $doc->id,
                'payment_type_id' => $paymentType?->id,
                'user_id' => auth()->id(),
                'amount' => $pay,
                'date' => now(),
            ]);
        }

        return response()->json([
            'message' => $excess > 0
                ? 'Payment recorded. ' . $allocated . ' applied, ' . $excess . ' overpaid (excess ignored).'
                : 'Payment allocated to invoice(s).',
            'data' => [
                'allocated' => $allocated,
                'remaining' => $remaining,
                'excess' => $excess,
            ],
        ]);
    }

    public function statement(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $customer = Customer::where(function ($q) { $q->where('tenant_id', auth()->user()->tenant_id)->orWhereNull('tenant_id'); })->findOrFail($id);

        $documents = \App\Models\Document::where('customer_id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $totalInvoiced = round((float) $documents->sum('total'), 4);
        $totalPaid = round((float) $documents->sum('paid_amount'), 4);
        $totalDue = round((float) $documents->sum('due_amount'), 4);

        $paymentRecords = \App\Models\Payment::with('paymentType:id,name')
            ->whereIn('document_id', $documents->pluck('id'))
            ->where('amount', '>', 0)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'customer' => $customer,
                'invoices' => $documents,
                'payments' => $paymentRecords,
                'summary' => [
                    'total_invoiced' => $totalInvoiced,
                    'total_paid' => $totalPaid,
                    'total_due' => $totalDue,
                ],
            ],
        ]);
    }

    public function payments(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $customer = Customer::where(function ($q) { $q->where('tenant_id', auth()->user()->tenant_id)->orWhereNull('tenant_id'); })->findOrFail($id);

        $documentIds = \App\Models\Document::where('customer_id', $id)->pluck('id');

        $payments = \App\Models\Payment::with('paymentType:id,name')
            ->whereIn('document_id', $documentIds)
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json(['data' => $payments]);
    }
}
