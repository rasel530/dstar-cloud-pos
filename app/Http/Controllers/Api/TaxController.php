<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $taxes = Tax::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['data' => $taxes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|max:255',
            'rate'       => 'required|numeric|min:0',
            'is_fixed'   => 'boolean',
            'is_enabled' => 'boolean',
        ]);
        $validated['tenant_id'] = auth()->user()->tenant_id;

        try {
            $tax = Tax::create($validated);
            return response()->json(['data' => $tax], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create tax'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $tax = Tax::where('tenant_id', auth()->user()->tenant_id)->find($id);
        if (!$tax) {
            return response()->json(['message' => 'Tax not found'], 404);
        }

        $validated = $request->validate([
            'name'       => 'sometimes|required|max:255',
            'rate'       => 'sometimes|required|numeric|min:0',
            'is_fixed'   => 'boolean',
            'is_enabled' => 'boolean',
        ]);

        try {
            $tax->update($validated);
            return response()->json(['data' => $tax], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update tax'], 500);
        }
    }

    public function destroy($id)
    {
        $tax = Tax::where('tenant_id', auth()->user()->tenant_id)->find($id);
        if (!$tax) {
            return response()->json(['message' => 'Tax not found'], 404);
        }

        try {
            $tax->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete tax'], 500);
        }
    }
}
