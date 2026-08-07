<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query()
            ->with([
                'customer',
                'user',
                'documentType',
                'warehouse',
                'documentItems.product',
                'payments.paymentType',
            ]);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('paid_status')) {
            $query->where('paid_status', $request->paid_status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('reference_document_number', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json(['data' => $documents]);
    }

    public function show($id)
    {
        $document = Document::with([
            'customer',
            'user',
            'documentType',
            'warehouse',
            'documentItems.product.productGroup',
            'documentItems.documentItemTaxes',
            'payments.paymentType',
        ])->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return response()->json(['data' => $document]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'                 => 'nullable|exists:customers,id',
            'document_type_id'            => 'required|exists:document_types,id',
            'warehouse_id'                => 'required|exists:warehouses,id',
            'date'                        => 'required|date',
            'discount'                    => 'nullable|numeric|min:0',
            'discount_type'               => 'nullable|integer',
            'note'                        => 'nullable|string',
            'internal_note'               => 'nullable|string',
            'due_date'                    => 'nullable|date',
            'reference_document_number'   => 'nullable|string',
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.quantity'            => 'required|numeric|min:0.001',
            'items.*.price'              => 'required|numeric|min:0',
            'items.*.discount'            => 'nullable|numeric|min:0',
            'items.*.discount_type'       => 'nullable|integer',
            'items.*.product_cost'        => 'nullable|numeric|min:0',
        ]);

        try {
            $documentNumber = $this->generateDocumentNumber();
            $items = $request->items;
            $itemTotal = collect($items)->sum(function ($item) {
                return $item['quantity'] * $item['price'] - ($item['discount'] ?? 0);
            });

            $discountAmount = 0;
            $documentDiscount = $request->discount ?? 0;
            if ($documentDiscount > 0) {
                $discountType = $request->discount_type ?? 0;
                $discountAmount = $discountType === 0
                    ? $documentDiscount
                    : $itemTotal * ($documentDiscount / 100);
            }

            $total = max(0, $itemTotal - $discountAmount);

            $document = Document::create([
                'tenant_id'                => auth()->user()->tenant_id,
                'number'                   => $documentNumber,
                'user_id'                  => $request->user()->id,
                'customer_id'              => $request->customer_id,
                'date'                     => $request->date,
                'stock_date'               => now(),
                'total'                    => $total,
                'discount'                 => $documentDiscount,
                'discount_type'            => $request->discount_type ?? 0,
                'discount_apply_rule'      => $request->discount_apply_rule ?? 0,
                'is_clocked_out'           => false,
                'document_type_id'         => $request->document_type_id,
                'warehouse_id'             => $request->warehouse_id,
                'reference_document_number' => $request->reference_document_number,
                'internal_note'            => $request->internal_note,
                'note'                     => $request->note,
                'due_date'                 => $request->due_date,
                'paid_status'              => 0,
                'service_type'             => $request->service_type ?? 0,
            ]);

            foreach ($items as $itemData) {
                $lineTotal = $itemData['quantity'] * $itemData['price'] - ($itemData['discount'] ?? 0);
                $lineDiscountedTotal = $itemTotal > 0
                    ? $lineTotal - ($lineTotal / $itemTotal * $discountAmount)
                    : 0;

                $document->documentItems()->create([
                    'product_id'                      => $itemData['product_id'],
                    'quantity'                        => $itemData['quantity'],
                    'expected_quantity'               => $itemData['quantity'],
                    'price'                           => $itemData['price'],
                    'price_before_tax'                => $itemData['price'],
                    'price_before_tax_after_discount' => max(0, $itemData['price'] - (($itemData['discount'] ?? 0) / max(1, $itemData['quantity']))),
                    'price_after_discount'            => max(0, ($lineTotal) / max(1, $itemData['quantity'])),
                    'discount'                        => $itemData['discount'] ?? 0,
                    'discount_type'                   => $itemData['discount_type'] ?? 0,
                    'discount_apply_rule'             => $itemData['discount_apply_rule'] ?? 0,
                    'product_cost'                    => $itemData['product_cost'] ?? 0,
                    'total'                           => max(0, $lineTotal),
                    'total_after_document_discount'   => max(0, $lineDiscountedTotal),
                ]);
            }

            $document->load([
                'customer',
                'user',
                'documentType',
                'warehouse',
                'documentItems.product',
            ]);

            return response()->json(['data' => $document], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create document'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $request->validate([
            'note'          => 'nullable|string',
            'internal_note' => 'nullable|string',
            'due_date'      => 'nullable|date',
            'paid_status'   => 'nullable|integer',
        ]);

        try {
            $document->update($request->only([
                'note',
                'internal_note',
                'due_date',
                'paid_status',
            ]));

            $document->load([
                'customer',
                'user',
                'documentType',
                'warehouse',
                'documentItems.product',
            ]);

            return response()->json(['data' => $document]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update document'], 500);
        }
    }

    public function getByDate(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $documents = Document::with([
            'customer',
            'user',
            'documentType',
            'warehouse',
            'documentItems.product',
            'payments.paymentType',
        ])
            ->whereDate('date', '>=', $request->date_from)
            ->whereDate('date', '<=', $request->date_to)
            ->orderBy('date', 'desc')
            ->paginate(25);

        return response()->json(['data' => $documents]);
    }

    public function getByCustomer(Request $request, $customerId)
    {
        $documents = Document::with([
            'customer',
            'user',
            'documentType',
            'warehouse',
            'documentItems.product',
            'payments.paymentType',
        ])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(['data' => $documents]);
    }

    public function getByType(Request $request, $typeId)
    {
        $documents = Document::with([
            'customer',
            'user',
            'documentType',
            'warehouse',
            'documentItems.product',
            'payments.paymentType',
        ])
            ->where('document_type_id', $typeId)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(['data' => $documents]);
    }

    private function generateDocumentNumber(): string
    {
        $prefix = 'DOC-';
        $date = now()->format('Ymd');
        $count = Document::whereDate('created_at', now()->toDateString())->count() + 1;
        return $prefix . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
