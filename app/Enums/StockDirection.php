<?php

namespace App\Enums;

enum StockDirection: int
{
    case None = 0;
    case In = 1;
    case Out = 2;

    public function multiplier(): int
    {
        return match ($this) {
            self::None => 0,
            self::In => 1,
            self::Out => -1,
        };
    }
}
