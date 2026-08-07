<?php

namespace App\Enums;

enum AccessLevel: int
{
    case Cashier = 0;
    case Manager = 5;
    case Admin = 9;

    public function label(): string
    {
        return match ($this) {
            self::Cashier => 'Cashier',
            self::Manager => 'Manager',
            self::Admin => 'Admin',
        };
    }

    public function canManageUsers(): bool
    {
        return $this->value >= self::Manager->value;
    }

    public function canVoidItems(): bool
    {
        return $this->value >= self::Manager->value;
    }

    public function canAccessReports(): bool
    {
        return $this->value >= self::Manager->value;
    }

    public function canManageSettings(): bool
    {
        return $this === self::Admin;
    }
}
