<?php

namespace App\Traits;

use App\Scopes\TenantScope;

trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}
