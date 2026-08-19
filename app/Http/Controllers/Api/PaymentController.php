<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with([
                'paymentType',
                'user',
                'document',
            ]);

        if ($request->has('document_id')) {
            $query->where('document_id', $request->document_id);
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->has('payment_type_id')) {
            $query->where('payment_type_id', $request->payment_type_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json(['data' => $payments]);
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'document_id'     => "required|exists:documents,id,tenant_id,$tenantId",
            'payment_type_id' => "required|exists:payment_types,id,tenant_id,$tenantId",
            'amount'          => 'required|numeric|min:0',
            'date'            => 'required|date',
        ]);

        $document = Document::where('tenant_id', $tenantId)->find($request->document_id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        try {
            $existingTotal = Payment::where('document_id', $document->id)->sum('amount');
            $newTotal = $existingTotal + $request->amount;

            $payment = Payment::create([
                'tenant_id'      => $document->tenant_id,
                'document_id'    => $document->id,
                'payment_type_id' => $request->payment_type_id,
                'user_id'        => $request->user()->id,
                'amount'         => $request->amount,
                'date'           => $request->date,
            ]);

            if ($newTotal >= $document->total && $document->paid_status !== 1) {
                $adjustment = round($newTotal - $document->total, 4);
                if (abs($adjustment) > 0.0001) {
                    $payment->rounding_adjustment = $adjustment;
                    $payment->save();
                }
                $document->paid_status = 1;
                $document->save();
            } elseif ($document->paid_status === 0 && $newTotal > 0) {
                $document->paid_status = 2;
                $document->save();
            }

            $payment->load(['paymentType', 'user', 'document']);

            return response()->json(['data' => $payment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create payment'], 500);
        }
    }

    public function show($id)
    {
        $payment = Payment::with([
            'paymentType',
            'user',
            'document.customer',
            'document.documentType',
        ])->where('tenant_id', auth()->user()->tenant_id)->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json(['data' => $payment]);
    }
}
