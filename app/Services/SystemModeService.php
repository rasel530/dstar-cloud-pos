<?php

namespace App\Services;

use App\Models\ApplicationSetting;

class SystemModeService
{
    const MODE_MULTI_BRANCH = 'multi_branch';
    const MODE_SINGLE = 'single';

    protected static ?string $cachedMode = null;

    public static function getMode(): string
    {
        if (self::$cachedMode !== null) {
            return self::$cachedMode;
        }

        try {
            $tenantId = session('tenant_id') ?? auth()->user()?->tenant_id;
            if (! $tenantId) {
                return self::MODE_MULTI_BRANCH;
            }

            $value = ApplicationSetting::where('tenant_id', $tenantId)
                ->where('key', 'system_mode')
                ->value('value');

            $value = is_string($value) ? trim($value, '"\'') : $value;
            self::$cachedMode = ($value === self::MODE_SINGLE) ? self::MODE_SINGLE : self::MODE_MULTI_BRANCH;
        } catch (\Exception $e) {
            self::$cachedMode = self::MODE_MULTI_BRANCH;
        }

        return self::$cachedMode;
    }

    public static function isSingleMode(): bool
    {
        return self::getMode() === self::MODE_SINGLE;
    }

    public static function isMultiBranchMode(): bool
    {
        return self::getMode() === self::MODE_MULTI_BRANCH;
    }

    public static function setMode(string $mode, ?string $tenantId = null): void
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        if (! $tenantId) return;

        ApplicationSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => 'system_mode'],
            ['value' => $mode === self::MODE_SINGLE ? self::MODE_SINGLE : self::MODE_MULTI_BRANCH]
        );

        self::$cachedMode = null;
    }

    public static function clearCache(): void
    {
        self::$cachedMode = null;
    }
}
