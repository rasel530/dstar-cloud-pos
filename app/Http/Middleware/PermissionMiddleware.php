<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    private function getModuleFromPath(string $path): ?string
    {
        $segments = explode('/', trim($path, '/'));
        if ($segments[0] === 'api' && isset($segments[1])) {
            return $segments[1];
        }
        return null;
    }

    private function getActionFromMethod(string $method): string
    {
        return match ($method) {
            'GET', 'HEAD' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'edit',
            'DELETE' => 'delete',
            default => 'view',
        };
    }

    public function handle(Request $request, Closure $next, string $module, string $action = 'view')
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $perms = config("rbac.modules.{$module}");
        if (! $perms) {
            return $next($request);
        }

        $requiredLevel = $perms[$action] ?? 9;

        if ($user->access_level < $requiredLevel) {
            return response()->json([
                'message' => "Access denied. Requires level {$requiredLevel} for {$module}:{$action}.",
            ], 403);
        }

        return $next($request);
    }
}
