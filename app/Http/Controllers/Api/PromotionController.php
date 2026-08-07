<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(): JsonResponse
    {
        $promotions = Promotion::where('tenant_id', auth()->user()->tenant_id)
            ->when(request('is_enabled') !== null, fn($q) => $q->where('is_enabled', request('is_enabled')))
            ->orderBy('created_at', 'desc')
            ->paginate(25);
        return response()->json($promotions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'days_of_week' => 'nullable',
            'is_enabled'   => 'boolean',
        ]);
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['days_of_week'] = (int) ($validated['days_of_week'] ?? 127);

        $promotion = Promotion::create($validated);

        return response()->json(['data' => $promotion], 201);
    }

    public function show($id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        return response()->json(['data' => $promotion]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'days_of_week' => 'nullable',
            'is_enabled'   => 'boolean',
        ]);
        if (isset($validated['days_of_week'])) {
            $validated['days_of_week'] = (int) $validated['days_of_week'];
        }

        $promotion->update($validated);

        return response()->json(['data' => $promotion]);
    }

    public function destroy($id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();

        return response()->json(null, 204);
    }

    public function toggleEnabled($id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->is_enabled = !$promotion->is_enabled;
        $promotion->save();

        return response()->json(['data' => $promotion]);
    }
}
