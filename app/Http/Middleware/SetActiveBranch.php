<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetActiveBranch
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if (\App\Services\SystemModeService::isSingleMode()) {
            session(['active_branch_id' => $user->tenant_id]);
            return $next($request);
        }

        $branchId = $request->header('X-Active-Branch')
            ?? session('active_branch_id')
            ?? $user->branch_id
            ?? $this->findHeadquarters($user->tenant_id);

        session(['active_branch_id' => $branchId]);

        return $next($request);
    }

    private function findHeadquarters(?string $tenantId): ?string
    {
        if (! $tenantId) return null;

        $tenant = \App\Models\Tenant::find($tenantId);
        if (! $tenant) return null;

        if ($tenant->is_headquarters) return $tenant->id;
        if ($tenant->company_id) {
            $hq = \App\Models\Tenant::where('id', $tenant->company_id)
                ->where('is_headquarters', true)
                ->value('id');
            if ($hq) return $hq;
        }

        return $tenantId;
    }
}
