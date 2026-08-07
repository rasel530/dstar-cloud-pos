<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FiscalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FiscalItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = FiscalItem::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('plu', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plu'  => 'required|string|max:50|unique:fiscal_items,plu',
            'name' => 'required|string|max:255',
            'vat'  => 'required|string|max:255',
        ]);

        try {
            $validated['tenant_id'] = auth()->user()->tenant_id;
            $item = FiscalItem::create($validated);
            return response()->json(['data' => $item], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create fiscal item'], 500);
        }
    }

    public function show($plu): JsonResponse
    {
        $item = FiscalItem::where('tenant_id', auth()->user()->tenant_id)->find($plu);
        if (!$item) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $item]);
    }

    public function update(Request $request, $plu): JsonResponse
    {
        $item = FiscalItem::where('tenant_id', auth()->user()->tenant_id)->find($plu);
        if (!$item) return response()->json(['message' => 'Not found'], 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat'  => 'required|string|max:255',
        ]);

        try {
            $item->update($validated);
            return response()->json(['data' => $item]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update'], 500);
        }
    }

    public function destroy($plu): JsonResponse
    {
        $item = FiscalItem::where('tenant_id', auth()->user()->tenant_id)->find($plu);
        if (!$item) return response()->json(['message' => 'Not found'], 404);

        try {
            $item->delete();
            return response()->json(['message' => 'Deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete'], 500);
        }
    }
}
