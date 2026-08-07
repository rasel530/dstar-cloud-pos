<?php

if (!function_exists('tenant')) {
    function tenant(?string $key = null): mixed
    {
        $tenantId = session('tenant_id') ?? auth()->user()?->tenant_id;

        if ($key === null) {
            return $tenantId ? \App\Models\Tenant::find($tenantId) : null;
        }

        if ($key === 'id') {
            return $tenantId;
        }

        $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;
        return $tenant?->{$key};
    }
}

if (!function_exists('active_branch_id')) {
    function active_branch_id(): ?string
    {
        return session('active_branch_id') ?? auth()->user()?->branch_id ?? auth()->user()?->tenant_id;
    }
}
