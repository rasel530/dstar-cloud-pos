<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'date' => $this->date ? $this->date->toIso8601String() : null,
            'total' => (float) $this->total,
            'discount' => (float) $this->discount,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                ];
            }),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? $item->product_name ?? '',
                        'quantity' => (float) $item->quantity,
                        'price' => (float) $item->price,
                        'discount' => (float) ($item->discount ?? 0),
                        'tax_amount' => (float) ($item->tax_amount ?? 0),
                        'total' => (float) ($item->total ?? ($item->quantity * $item->price - ($item->discount ?? 0))),
                    ];
                })->toArray();
            }),
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'type' => $payment->paymentType ? [
                            'id' => $payment->paymentType->id,
                            'name' => $payment->paymentType->name,
                        ] : null,
                        'method' => $payment->paymentType->name ?? $payment->method ?? '',
                    ];
                })->toArray();
            }),
            'document_type' => $this->whenLoaded('documentType', function () {
                return [
                    'id' => $this->documentType->id,
                    'name' => $this->documentType->name,
                    'code' => $this->documentType->code ?? '',
                ];
            }),
        ];
    }
}
