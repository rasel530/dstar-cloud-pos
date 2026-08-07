<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserEnabled
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && !$user->is_enabled) {
            $user->tokens()->delete();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Account is disabled.'], 403);
            }

            auth()->guard('web')->logout();
            return redirect('/login')->withErrors(['email' => 'Account is disabled.']);
        }

        return $next($request);
    }
}
