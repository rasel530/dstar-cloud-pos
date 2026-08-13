<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $shifts = Shift::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })
            ->orderBy('ordinal')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $shifts]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $validated = $request->validate([
            'name' => [
                'required', 'max:100',
                Rule::unique('shifts')->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                }),
            ],
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'ordinal' => 'nullable|integer',
            'is_enabled' => 'nullable|boolean',
        ]);

        $shift = Shift::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'ordinal' => $validated['ordinal'] ?? 0,
            'is_enabled' => $validated['is_enabled'] ?? true,
        ]);

        return response()->json(['data' => $shift], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'sometimes', 'required', 'max:100',
                Rule::unique('shifts')->where(function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                })->ignore($id),
            ],
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'ordinal' => 'nullable|integer',
            'is_enabled' => 'nullable|boolean',
        ]);

        $shift->update($validated);

        return response()->json(['data' => $shift]);
    }

    public function destroy(string $id): JsonResponse
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return response()->json(['message' => 'Shift deleted.']);
    }
}
