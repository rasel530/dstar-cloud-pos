<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PosSessionMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($request->user() && session('active_branch_id')) {
            $response->headers->set('X-Active-Branch-Id', session('active_branch_id'));
            $response->headers->set('X-Tenant-Id', session('tenant_id'));
        }

        return $response;
    }
}
