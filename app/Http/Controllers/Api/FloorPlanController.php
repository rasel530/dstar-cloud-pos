<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FloorPlan;
use App\Models\FloorPlanTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FloorPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = FloorPlan::where('tenant_id', auth()->user()->tenant_id)
            ->with('floorPlanTables')
            ->orderBy('name')
            ->paginate(25);

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $floorPlan = FloorPlan::create($validated);

        return response()->json([
            'message' => 'Floor plan created successfully.',
            'data' => $floorPlan,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('floorPlanTables')
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        return response()->json($floorPlan);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $floorPlan->update($validated);

        return response()->json([
            'message' => 'Floor plan updated successfully.',
            'data' => $floorPlan->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        $floorPlan->floorPlanTables()->delete();
        $floorPlan->delete();

        return response()->json(['message' => 'Floor plan deleted successfully.']);
    }

    public function addTable(Request $request, string $id): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'is_round' => 'nullable|boolean',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['floor_plan_id'] = $floorPlan->id;

        $table = FloorPlanTable::create($validated);

        return response()->json([
            'message' => 'Table added successfully.',
            'data' => $table,
        ], 201);
    }

    public function updateTable(Request $request, string $id, string $tableId): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        $table = FloorPlanTable::where('id', $tableId)
            ->where('floor_plan_id', $floorPlan->id)
            ->first();

        if (!$table) {
            return response()->json(['message' => 'Table not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'is_round' => 'nullable|boolean',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table updated successfully.',
            'data' => $table->fresh(),
        ]);
    }

    public function removeTable(Request $request, string $id, string $tableId): JsonResponse
    {
        $floorPlan = FloorPlan::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$floorPlan) {
            return response()->json(['message' => 'Floor plan not found.'], 404);
        }

        $table = FloorPlanTable::where('id', $tableId)
            ->where('floor_plan_id', $floorPlan->id)
            ->first();

        if (!$table) {
            return response()->json(['message' => 'Table not found.'], 404);
        }

        $table->delete();

        return response()->json(['message' => 'Table removed successfully.']);
    }
}
