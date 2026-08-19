<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SetActiveBranch
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user() ?? $this->resolveUserFromToken($request);
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

        // Security: never trust a client-supplied branch outside the user's allowed set.
        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $branchId = $user->branch_id ?? $this->findHeadquarters($user->tenant_id);
            if ($branchId && ! $user->canAccessBranch($branchId)) {
                $branchId = $user->tenant_id;
            }
        }

        if ($branchId) {
            session(['active_branch_id' => $branchId]);
        }

        return $next($request);
    }

    private function resolveUserFromToken(Request $request): mixed
    {
        $token = $request->bearerToken();
        if (! $token) return null;

        $accessToken = PersonalAccessToken::findToken($token);
        if (! $accessToken) return null;

        $accessToken->forceFill(['last_used_at' => now()])->save();

        return $accessToken->tokenable;
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
