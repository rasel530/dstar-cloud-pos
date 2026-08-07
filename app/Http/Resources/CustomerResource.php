<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_enabled' => (bool) $this->is_enabled,
            'loyalty_card' => $this->whenLoaded('loyaltyCard', function () {
                return [
                    'id' => $this->loyaltyCard->id,
                    'card_number' => $this->loyaltyCard->card_number,
                    'points' => (int) $this->loyaltyCard->points,
                    'tier' => $this->loyaltyCard->tier ?? null,
                ];
            }),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
