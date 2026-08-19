<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?? $this->resolveUserFromToken($request);

        if ($user && $user->tenant_id) {
            session(['tenant_id' => $user->tenant_id]);
        }

        return $next($request);
    }

    private function resolveUserFromToken(Request $request): mixed
    {
        $token = $request->bearerToken();
        if (! $token) return null;

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }
}
