<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Product;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Barcode::query()
            ->whereHas('product', fn($q) => $q->where(fn($q2) => $q2->where('tenant_id', $tenantId)->orWhere('is_global', true)))
            ->with(['product:id,name,code,price,product_group_id,track_inventory,is_enabled'])
            ->orderBy('product_id')
            ->orderBy('is_primary', 'desc');

        if ($search = $request->query('search')) {
            $query->where('value', 'ilike', "%{$search}%");
        }

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->query('barcode_type')) {
            $query->where('barcode_type', $type);
        }

        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        $barcodes = $query->paginate($request->query('per_page', 25));

        return response()->json($barcodes);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'product_id' => "required|uuid|exists:products,id,tenant_id,$tenantId",
            'value' => 'required|string|max:255',
            'barcode_type' => 'in:CODE_128,EAN_13,UPC_A',
            'is_primary' => 'boolean',
        ]);

        $service = app(BarcodeService::class);
        if ($service->isDuplicate($validated['value'])) {
            $existing = Barcode::where('value', $validated['value'])->with('product:id,name')->first();
            return response()->json([
                'message' => 'Barcode already assigned to ' . ($existing->product->name ?? 'another product') . '.',
            ], 422);
        }

        $barcode = Barcode::create([
            'product_id' => $validated['product_id'],
            'value' => $validated['value'],
            'barcode_type' => $validated['barcode_type'] ?? 'CODE_128',
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        $barcode->load('product:id,name,code,price');

        return response()->json(['data' => $barcode], 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $barcode = Barcode::whereHas('product', fn($q) => $q->where(fn($q2) => $q2->where('tenant_id', $tenantId)->orWhere('is_global', true)))
            ->with('product:id,name,code,price')
            ->findOrFail($id);
        return response()->json(['data' => $barcode]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $barcode = Barcode::whereHas('product', fn($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($id);

        $validated = $request->validate([
            'value' => 'sometimes|string|max:255',
            'barcode_type' => 'in:CODE_128,EAN_13,UPC_A',
            'is_primary' => 'boolean',
            'is_enabled' => 'boolean',
        ]);

        if (isset($validated['value']) && $validated['value'] !== $barcode->value) {
            $service = app(BarcodeService::class);
            if ($service->isDuplicate($validated['value'], $id)) {
                return response()->json(['message' => 'Barcode already exists.'], 422);
            }
        }

        $barcode->update($validated);
        $barcode->load('product:id,name,code,price');

        return response()->json(['data' => $barcode]);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $barcode = Barcode::whereHas('product', fn($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($id);
        $barcode->update(['is_enabled' => false]);
        return response()->json(['message' => 'Barcode deactivated.']);
    }

    public function generate(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'product_id' => "nullable|uuid|exists:products,id,tenant_id,$tenantId",
            'barcode_type' => 'in:CODE_128,EAN_13,UPC_A',
        ]);

        $type = $validated['barcode_type'] ?? 'CODE_128';
        $service = app(BarcodeService::class);

        $attempts = 0;
        do {
            $value = $service->generate($type);
            $attempts++;
        } while ($service->isDuplicate($value) && $attempts < 10);

        if (!empty($validated['product_id'])) {
            $existing = Barcode::where('product_id', $validated['product_id'])
                ->where('is_primary', true)
                ->first();

            if ($existing) {
                $existing->update([
                    'value' => $value,
                    'barcode_type' => $type,
                ]);
                $barcode = $existing;
            } else {
                $barcode = Barcode::create([
                    'product_id' => $validated['product_id'],
                    'value' => $value,
                    'barcode_type' => $type,
                    'is_primary' => true,
                ]);
            }

            $barcode->load('product:id,name,code,price');

            return response()->json(['data' => $barcode], 201);
        }

        return response()->json(['data' => ['value' => $value, 'barcode_type' => $type]]);
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate(['value' => 'required|string']);
        $tenantId = auth()->user()->tenant_id;

        $barcode = Barcode::where('value', $request->value)
            ->where('is_enabled', true)
            ->whereHas('product', fn($q) => $q->where(fn($q2) => $q2->where('tenant_id', $tenantId)->orWhere('is_global', true)))
            ->with('product:id,name,code,price,product_group_id,track_inventory')
            ->first();

        if (!$barcode || !$barcode->product || !$barcode->product->is_enabled) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['data' => $barcode->product]);
    }

    public function productsWithoutBarcode(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_enabled', true)
            ->whereDoesntHave('barcodes', fn($q) => $q->where('is_enabled', true))
            ->select('id', 'name', 'code', 'price')
            ->orderBy('name')
            ->paginate($request->query('per_page', 50));

        return response()->json($products);
    }

    public function bulkGenerate(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => "uuid|exists:products,id,tenant_id,$tenantId",
            'barcode_type' => 'in:CODE_128,EAN_13,UPC_A',
        ]);

        $type = $validated['barcode_type'] ?? 'CODE_128';
        $service = app(BarcodeService::class);
        $generated = [];

        foreach ($validated['product_ids'] as $productId) {
            $existing = Barcode::where('product_id', $productId)
                ->where('is_primary', true)
                ->first();

            if ($existing) {
                $generated[] = $existing;
                continue;
            }

            $attempts = 0;
            do {
                $value = $service->generate($type);
                $attempts++;
            } while ($service->isDuplicate($value) && $attempts < 10);

            $barcode = Barcode::create([
                'product_id' => $productId,
                'value' => $value,
                'barcode_type' => $type,
                'is_primary' => true,
            ]);

            $generated[] = $barcode;
        }

        $generated = Barcode::whereIn('id', collect($generated)->pluck('id'))
            ->with('product:id,name,code,price')
            ->get();

        return response()->json([
            'data' => $generated,
            'count' => $generated->count(),
        ]);
    }

    public function print(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid|exists:barcodes,id',
            'label_size' => 'in:small,medium,large',
        ]);

        $barcodes = Barcode::whereIn('id', $validated['ids'])
            ->where('is_enabled', true)
            ->whereHas('product', fn($q) => $q->where('tenant_id', $tenantId))
            ->with('product:id,name,code,price')
            ->get();

        if ($barcodes->isEmpty()) {
            return response()->json(['message' => 'No valid barcodes to print.'], 422);
        }

        // Build ESC/POS print data
        $labelSize = $validated['label_size'] ?? 'medium';
        $labelWidths = ['small' => 30, 'medium' => 40, 'large' => 52];

        $commands = [];
        foreach ($barcodes as $barcode) {
            $product = $barcode->product;
            $commands[] = [
                'type' => 'barcode_label',
                'barcode_value' => $barcode->value,
                'barcode_type' => $barcode->barcode_type,
                'product_name' => $product->name ?? 'N/A',
                'sku' => $product->code ?? '',
                'price' => number_format((float) ($product->price ?? 0), 2),
                'label_width' => $labelWidths[$labelSize] ?? 40,
            ];
        }

        return response()->json([
            'data' => [
                'print_commands' => $commands,
                'label_count' => count($commands),
                'label_size' => $labelSize,
            ],
        ]);
    }
}
