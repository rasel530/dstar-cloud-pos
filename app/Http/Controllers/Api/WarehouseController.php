<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query()
            ->withCount(['stocks', 'documents', 'stockMovements']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('is_default')) {
            $query->where('is_default', filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN));
        }

        $warehouses = $query->orderBy('name')->paginate(25);

        return response()->json(['data' => $warehouses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            if ($request->is_default) {
                Warehouse::where('is_default', true)->update(['is_default' => false]);
            }

            $warehouse = Warehouse::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'name'       => $request->name,
                'is_default' => $request->is_default ?? false,
            ]);

            return response()->json(['data' => $warehouse], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create warehouse'], 500);
        }
    }

    public function show($id)
    {
        $warehouse = Warehouse::withCount(['stocks', 'documents', 'stockMovements'])
            ->find($id);

        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        $stockSummary = $warehouse->stocks()
            ->with('product')
            ->get()
            ->groupBy(fn ($s) => $s->product_id);

        return response()->json([
            'data' => [
                'warehouse'    => $warehouse,
                'stock_count'  => $stockSummary->count(),
                'total_items'  => $warehouse->stocks()->sum('quantity'),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            if ($request->is_default && !$warehouse->is_default) {
                Warehouse::where('is_default', true)->update(['is_default' => false]);
            }

            $warehouse->update($request->only(['name', 'is_default']));

            return response()->json(['data' => $warehouse]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update warehouse'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $warehouse = Warehouse::withCount(['stocks', 'documents'])->find($id);

        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        if ($warehouse->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default warehouse',
            ], 422);
        }

        if (($warehouse->stocks_count > 0 || $warehouse->documents_count > 0) && !$request->has('force')) {
            return response()->json([
                'message' => 'Warehouse has ' . $warehouse->stocks_count . ' stock records and ' . $warehouse->documents_count . ' documents.',
                'stocks_count' => $warehouse->stocks_count,
                'documents_count' => $warehouse->documents_count,
            ], 422);
        }

        try {
            $warehouse->stockMovements()->delete();
            $warehouse->stocks()->delete();
            $warehouse->documents()->delete();
            $warehouse->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete warehouse'], 500);
        }
    }
}
