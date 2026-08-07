<?php

namespace App\Http\Middleware;

use App\Enums\AccessLevel;
use Closure;
use Illuminate\Http\Request;

class CheckAccessLevel
{
    public function handle(Request $request, Closure $next, int $minimumLevel): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->is_enabled) {
            return response()->json(['message' => 'Account disabled or not found.'], 403);
        }

        if ($user->access_level < $minimumLevel) {
            return response()->json(['message' => 'Insufficient access level.'], 403);
        }

        return $next($request);
    }
}
