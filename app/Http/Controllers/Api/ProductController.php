<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Product::query()->with(['productGroup', 'barcodes', 'taxes', 'stocks.warehouse', 'branchInventories.branch']);

        $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhere('is_global', true);
        });

        if ($request->filled('product_group_id')) {
            $query->where('product_group_id', $request->product_group_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('code', 'ilike', "%{$term}%")
                  ->orWhereHas('barcodes', fn($b) => $b->where('value', 'ilike', "%{$term}%"));
            });
        }

        $perPage = $request->filled('per_page') ? (int) $request->per_page : 25;
        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json(['data' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|max:255',
            'code'              => 'nullable|string',
            'plu'               => 'nullable|integer',
            'price'             => 'nullable|numeric',
            'mrp'               => 'nullable|numeric',
            'cost'              => 'nullable|numeric',
            'product_group_id'  => 'nullable|exists:product_groups,id',
            'track_inventory'   => 'boolean',
            'is_global'         => 'boolean',
        ]);

        try {
            $data = $request->all();
            $data['tenant_id'] = auth()->user()->tenant_id;
            $product = Product::create($data);

            if ($request->filled('barcode')) {
                Barcode::create([
                    'product_id' => $product->id,
                    'value' => $request->barcode,
                    'barcode_type' => $request->barcode_type ?? 'CODE_128',
                    'is_primary' => true,
                ]);
            }

            $product->load('barcodes');
            return response()->json(['data' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product'], 500);
        }
    }

    public function show($id)
    {
        $product = Product::with(['productGroup', 'barcodes', 'taxes'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['data' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name'              => 'sometimes|required|max:255',
            'code'              => 'nullable|string',
            'plu'               => 'nullable|integer',
            'price'             => 'nullable|numeric',
            'mrp'               => 'nullable|numeric',
            'cost'              => 'nullable|numeric',
            'product_group_id'  => 'nullable|exists:product_groups,id',
        ]);

        try {
            $product->update($request->all());

            if ($request->filled('barcode')) {
                $existing = Barcode::where('product_id', $product->id)->where('is_primary', true)->first();
                if ($existing && $existing->value !== $request->barcode) {
                    $existing->update(['value' => $request->barcode, 'barcode_type' => $request->barcode_type ?? 'CODE_128']);
                } elseif (!$existing) {
                    Barcode::create([
                        'product_id' => $product->id,
                        'value' => $request->barcode,
                        'barcode_type' => $request->barcode_type ?? 'CODE_128',
                        'is_primary' => true,
                    ]);
                }
            }

            $product->load('barcodes');
            return response()->json(['data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product'], 500);
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $product->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
    }
}
