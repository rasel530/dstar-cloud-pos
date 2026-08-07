<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'plu' => $this->plu,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'product_group' => $this->whenLoaded('productGroup', function () {
                return [
                    'id' => $this->productGroup->id,
                    'name' => $this->productGroup->name,
                ];
            }),
            'barcodes' => $this->whenLoaded('barcodes', function () {
                return $this->barcodes->pluck('barcode')->toArray();
            }),
            'taxes' => $this->whenLoaded('taxes', function () {
                return $this->taxes->map(function ($tax) {
                    return [
                        'id' => $tax->id,
                        'name' => $tax->name,
                        'rate' => (float) $tax->rate,
                    ];
                })->toArray();
            }),
            'is_enabled' => (bool) $this->is_enabled,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
