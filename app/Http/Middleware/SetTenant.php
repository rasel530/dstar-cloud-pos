<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            session(['tenant_id' => $user->tenant_id]);
        }

        return $next($request);
    }
}
