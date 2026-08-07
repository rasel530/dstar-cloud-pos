<?php

namespace App\Enums;

enum DiscountType: int
{
    case Percent = 0;
    case Flat = 1;

    public function calculate(float $amount, float $value): float
    {
        return match ($this) {
            self::Percent => $amount * ($value / 100),
            self::Flat => $value,
        };
    }
}
