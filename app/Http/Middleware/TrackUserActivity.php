<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserActivityLog;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $user = $request->user();
        if (! $user) return $response;

        $path = $request->path();
        if (str_starts_with($path, 'api/activity')) return $response;

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) return $response;

        $module = $this->detectModule($path);
        if (! $module) return $response;

        $action = $this->buildDescription($request, $module);

        UserActivityLog::create([
            'user_id'    => $user->id,
            'tenant_id'  => $user->tenant_id,
            'module'     => $module,
            'action'     => $action,
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'ip_address' => $request->ip(),
        ]);

        return $response;
    }

    private function buildDescription(Request $request, string $module): string
    {
        $method = $request->method();
        $segments = explode('/', trim($request->path(), '/'));
        $resource = $segments[1] ?? '';
        $id = $segments[2] ?? null;

        if ($id && preg_match('/^[0-9a-f-]{36}$/i', $id)) { $resourceId = $id; }
        else { $resourceId = null; }

        $descriptions = [
            'POST'    => fn() => $this->postDescription($resource, $request),
            'PUT'     => fn() => "{$module} #{$resourceId} was updated",
            'PATCH'   => fn() => "{$module} #{$resourceId} was updated",
            'DELETE'  => fn() => "{$module} #{$resourceId} was deleted",
        ];

        return isset($descriptions[$method]) ? $descriptions[$method]() : "{$module} {$method} {$resourceId}";
    }

    private function postDescription(string $resource, Request $request): string
    {
        $body = $request->all();
        $name = $body['name'] ?? $body['first_name'] ?? '';

        $specials = [
            'login'       => fn() => 'Logged in',
            'logout'      => fn() => 'Logged out',
            'pin-login'   => fn() => 'Logged in via PIN',
            'checkout'    => fn() => 'Completed order checkout',
            'close'       => fn() => 'Closed order',
            'refund'      => fn() => 'Refunded order',
            'switch'      => fn() => 'Switched branch',
        ];

        foreach ($specials as $key => $fn) {
            if (str_contains($request->path(), $key)) return $fn();
        }

        if ($name) {
            return "Created '{$name}'";
        }

        if ($resource === 'orders') return 'Created new order';
        if ($resource === 'stock') return 'Stock adjustment';

        return "Created new {$resource} item";
    }

    private function detectModule(string $path): ?string
    {
        $modules = config('modules.list', []);
        foreach ($modules as $key => $info) {
            if (str_contains($path, $key)) {
                return $info['label'];
            }
        }
        return null;
    }
}
