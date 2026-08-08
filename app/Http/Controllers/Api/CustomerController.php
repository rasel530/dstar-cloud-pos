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
        $query = Customer::query()
            ->select('customers.*')
            ->selectRaw('COALESCE((SELECT SUM(points_balance) FROM loyalty_cards WHERE customer_id = customers.id), 0) as loyalty_points');

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
}
