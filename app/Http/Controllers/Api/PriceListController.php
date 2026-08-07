<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\PriceListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    public function index(): JsonResponse
    {
        $priceLists = PriceList::with('items')->paginate(25);

        return response()->json($priceLists);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $priceList = PriceList::create($validated);

        return response()->json([
            'message'    => 'Price list created successfully.',
            'price_list' => $priceList,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $priceList = PriceList::with('items')->findOrFail($id);

        return response()->json([
            'price_list' => $priceList,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $priceList = PriceList::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $priceList->update($validated);

        return response()->json([
            'message'    => 'Price list updated successfully.',
            'price_list' => $priceList,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $priceList = PriceList::findOrFail($id);
        $priceList->delete();

        return response()->json([
            'message' => 'Price list deleted successfully.',
        ]);
    }

    public function addItem(Request $request, int $priceListId): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price'      => 'required|numeric|min:0',
        ]);

        $priceList = PriceList::findOrFail($priceListId);

        $item = $priceList->items()->create($validated);

        return response()->json([
            'message' => 'Item added to price list successfully.',
            'item'    => $item,
        ], 201);
    }

    public function removeItem(int $priceListId, int $itemId): JsonResponse
    {
        $item = PriceListItem::where('price_list_id', $priceListId)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'message' => 'Item removed from price list successfully.',
        ]);
    }
}
