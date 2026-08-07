<?php

use App\Http\Middleware\SetTenant;
use App\Http\Middleware\SetActiveBranch;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetTenant::class,
        ]);

        $middleware->api(append: [
            SetTenant::class,
            SetActiveBranch::class,
        ]);

        $middleware->alias([
            'access.level' => \App\Http\Middleware\CheckAccessLevel::class,
            'permission'   => \App\Http\Middleware\PermissionMiddleware::class,
            'user.enabled' => \App\Http\Middleware\EnsureUserEnabled::class,
            'track.activity' => \App\Http\Middleware\TrackUserActivity::class,
            'set.branch' => SetActiveBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
