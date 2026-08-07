<?php

namespace App\Enums;

enum MovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Refund = 'refund';
    case Adjustment = 'adjustment';
    case Loss = 'loss';
    case InventoryCount = 'inventory_count';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sale => 'Sale',
            self::Refund => 'Refund',
            self::Adjustment => 'Adjustment',
            self::Loss => 'Loss',
            self::InventoryCount => 'Inventory Count',
        };
    }
}
