<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionItemController extends Controller
{
    public function index(Promotion $promotion): JsonResponse
    {
        $items = $promotion->promotionItems()
            ->leftJoin('products', 'promotion_items.uid', '=', \DB::raw('products.id::text'))
            ->select('promotion_items.*', 'products.name as product_name')
            ->get()
            ->map(function ($item) {
                $item->product_name = $item->product_name ?? 'Product #' . $item->uid;
                return $item;
            });

        return response()->json(['data' => $items]);
    }

    public function store(Request $request, Promotion $promotion): JsonResponse
    {
        $validated = $request->validate([
            'product_id'    => 'required|string|exists:products,id',
            'discount_type' => 'integer|in:0,1',
            'value'         => 'required|numeric|min:0',
        ]);

        $item = $promotion->promotionItems()->create([
            'uid'           => $validated['product_id'],
            'discount_type' => $validated['discount_type'] ?? 0,
            'value'         => $validated['value'],
        ]);

        return response()->json(['data' => $item], 201);
    }

    public function destroy(Promotion $promotion, PromotionItem $item): JsonResponse
    {
        $item->delete();
        return response()->json(null, 204);
    }
}
