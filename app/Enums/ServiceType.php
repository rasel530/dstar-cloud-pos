<?php

namespace App\Enums;

enum ServiceType: int
{
    case DineIn = 0;
    case Takeaway = 1;

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Takeaway => 'Takeaway',
        };
    }
}
