<?php

namespace App\Observers;

use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class StockMovementObserver
{
    public function created(StockMovement $movement): void
    {
        Log::info('Stock movement recorded', [
            'movement_id' => $movement->id,
            'product_id' => $movement->product_id,
            'product_name' => $movement->product->name ?? 'Unknown',
            'type' => $movement->type,
            'quantity' => $movement->quantity,
            'warehouse_id' => $movement->warehouse_id,
            'reference' => $movement->reference ?? '',
            'tenant_id' => $movement->tenant_id,
            'created_at' => $movement->created_at->toIso8601String(),
        ]);

        if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
            activity()
                ->performedOn($movement)
                ->withProperties([
                    'product_id' => $movement->product_id,
                    'product_name' => $movement->product->name ?? null,
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                    'warehouse_id' => $movement->warehouse_id,
                    'reference' => $movement->reference,
                ])
                ->log('stock_movement_created');
        }
    }
}
