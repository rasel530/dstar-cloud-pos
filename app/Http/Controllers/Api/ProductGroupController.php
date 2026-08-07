<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function index()
    {
        $groups = ProductGroup::with(['children', 'parent'])->get();

        return response()->json(['data' => $groups]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|max:255',
            'parent_group_id' => 'nullable|exists:product_groups,id',
            'color'           => 'nullable|string',
            'rank'            => 'nullable|integer',
        ]);

        try {
            $group = ProductGroup::create($request->all());

            return response()->json(['data' => $group], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product group'], 500);
        }
    }

    public function show($id)
    {
        $group = ProductGroup::with(['children', 'parent'])->find($id);

        if (!$group) {
            return response()->json(['message' => 'Product group not found'], 404);
        }

        return response()->json(['data' => $group]);
    }

    public function update(Request $request, $id)
    {
        $group = ProductGroup::find($id);

        if (!$group) {
            return response()->json(['message' => 'Product group not found'], 404);
        }

        $request->validate([
            'name'            => 'required|max:255',
            'parent_group_id' => 'nullable|exists:product_groups,id',
            'color'           => 'nullable|string',
            'rank'            => 'nullable|integer',
        ]);

        try {
            $group->update($request->all());

            return response()->json(['data' => $group], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product group'], 500);
        }
    }

    public function destroy($id)
    {
        $group = ProductGroup::find($id);

        if (!$group) {
            return response()->json(['message' => 'Product group not found'], 404);
        }

        try {
            $group->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product group'], 500);
        }
    }
}
