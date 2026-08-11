<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $types = PaymentType::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('is_enabled', true)
            ->where('is_quick_payment', true)
            ->orderBy('ordinal')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $types]);
    }

    public function all(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $types = PaymentType::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->select('id', 'name', 'code', 'shortcut_key', 'is_quick_payment', 'is_enabled', 'ordinal', 'tenant_id')
            ->orderBy('ordinal')
            ->orderBy('name')
            ->get();

        $seen = [];
        $unique = [];
        foreach ($types as $type) {
            $key = strtolower($type->name);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $type;
            }
        }

        return response()->json(['data' => $unique]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_quick_payment' => 'boolean',
            'is_enabled' => 'boolean',
            'shortcut_key' => 'nullable|string|max:10',
            'ordinal' => 'integer',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_quick_payment'] ??= true;
        $validated['is_enabled'] ??= true;

        $type = PaymentType::create($validated);

        return response()->json(['data' => $type], 201);
    }

    public function show(string $id): JsonResponse
    {
        $type = $this->findType($id);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $type = $this->findType($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_quick_payment' => 'boolean',
            'is_enabled' => 'boolean',
            'shortcut_key' => 'nullable|string|max:10',
            'ordinal' => 'integer',
        ]);

        $type->update($validated);

        return response()->json(['data' => $type]);
    }

    public function destroy(string $id): JsonResponse
    {
        $type = $this->findType($id);

        if ($type->payments()->exists()) {
            return response()->json(['message' => 'Cannot delete payment type with existing transactions.'], 422);
        }

        $type->delete();

        return response()->json(['message' => 'Payment type deleted successfully.']);
    }

    private function findType(string $id): PaymentType
    {
        $tenantId = auth()->user()->tenant_id;

        return PaymentType::query()
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->findOrFail($id);
    }
}
