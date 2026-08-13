<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserActivityLog;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $response = $next($request);

        if (! $user) return $response;

        $path = $request->path();
        if (str_starts_with($path, 'api/activity')) return $response;

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) return $response;

        $module = $this->detectModule($path);
        if (! $module) return $response;

        $event = $this->detectEvent($request);
        $action = $this->buildDescription($request, $module);
        $reference = $this->detectReference($request);

        UserActivityLog::create([
            'user_id'    => $user->id,
            'tenant_id'  => $user->tenant_id,
            'branch_id'  => $request->header('X-Active-Branch'),
            'module'     => $module,
            'action'     => $action,
            'event'      => $event,
            'reference'  => $reference,
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'ip_address' => $request->ip(),
            'device'     => $this->detectDevice($request),
            'details'    => $this->safeDetails($request),
        ]);

        return $response;
    }

    private function detectEvent(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        if (str_contains($path, 'login')) return 'login';
        if (str_contains($path, 'logout')) return 'logout';
        if (str_contains($path, 'cash-register/open')) return 'register_opened';
        if (str_contains($path, 'cash-register/close')) return 'register_closed';
        if (str_contains($path, 'cash-register/cash-in-out')) return 'cash_in_out';
        if (str_contains($path, 'checkout')) return 'completed';
        if (str_contains($path, 'refund')) return 'refunded';
        if (str_contains($path, 'hold')) return 'held';
        if (str_contains($path, 'cancel')) return 'cancelled';
        if (str_contains($path, 'receive')) return 'received';
        if (str_contains($path, 'payment')) return 'paid';

        return match ($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => strtolower($method),
        };
    }

    private function detectReference(Request $request): ?string
    {
        $segments = explode('/', trim($request->path(), '/'));
        $id = $segments[2] ?? null;
        if ($id && preg_match('/^[0-9a-f-]{36}$/i', $id)) return $id;
        if ($id && ! is_numeric($id) && ! in_array($id, ['open', 'close', 'hold', 'resume', 'cancel', 'checkout', 'refund', 'payment', 'receive', 'items', 'void', 'transfer', 'cash-in-out', 'next-code', 'next-number', 'quick-list', 'hold-list', 'bulk-generate', 'products-without', 'reorder', 'quick', 'summary', 'scan'])) {
            return $id;
        }
        $body = $request->all();
        return $body['number'] ?? $body['code'] ?? $body['purchase_number'] ?? $body['reference_number'] ?? null;
    }

    private function detectDevice(Request $request): string
    {
        $ua = $request->header('User-Agent', '');
        $browser = 'Unknown';
        $os = 'Unknown';
        if (preg_match('/Chrome\/(\d+)/', $ua)) $browser = 'Chrome';
        elseif (preg_match('/Firefox\/(\d+)/', $ua)) $browser = 'Firefox';
        elseif (preg_match('/Safari\/(\d+)/', $ua) && ! str_contains($ua, 'Chrome')) $browser = 'Safari';
        elseif (str_contains($ua, 'Edg/')) $browser = 'Edge';
        if (str_contains($ua, 'Windows')) $os = 'Windows';
        elseif (str_contains($ua, 'Mac OS')) $os = 'macOS';
        elseif (str_contains($ua, 'Android')) $os = 'Android';
        elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
        elseif (str_contains($ua, 'Linux')) $os = 'Linux';
        return $browser . ' / ' . $os;
    }

    private function safeDetails(Request $request): ?array
    {
        $data = $request->all();
        if (empty($data)) return null;
        $sensitive = ['password', 'password_confirmation', 'pin', 'pin_code', 'token', 'current_password'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) $data[$key] = '[REDACTED]';
        }
        return array_slice($data, 0, 30);
    }

    private function buildDescription(Request $request, string $module): string
    {
        $method = $request->method();
        $segments = explode('/', trim($request->path(), '/'));
        $resource = $segments[1] ?? '';
        $id = $segments[2] ?? null;

        $resourceId = ($id && preg_match('/^[0-9a-f-]{36}$/i', $id)) ? $id : null;

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
            'login'          => fn() => 'Logged in',
            'pin-login'      => fn() => 'Logged in via PIN',
            'logout'         => fn() => 'Logged out',
            'checkout'       => fn() => 'Completed order checkout',
            'close'          => fn() => 'Closed order',
            'refund'         => fn() => 'Refunded order',
            'hold'           => fn() => 'Held order',
            'resume'         => fn() => 'Resumed order',
            'cancel'         => fn() => 'Cancelled order',
            'cash-register/open'        => fn() => 'Opened cash register',
            'cash-register/close'       => fn() => 'Closed cash register',
            'cash-register/cash-in-out' => fn() => ($body['type'] ?? '') === 'in' ? 'Cash in' : 'Cash out',
            'receive'        => fn() => 'Received purchase',
            'mark-paid'      => fn() => 'Marked purchase as paid',
            'payment'        => fn() => 'Recorded payment',
            'switch'         => fn() => 'Switched branch',
        ];

        foreach ($specials as $key => $fn) {
            if (str_contains($request->path(), $key)) return $fn();
        }

        if ($name) return "Created '{$name}'";
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
