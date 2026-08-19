<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/tenant.php');
    }

    public function boot(): void
    {
        try {
            $tz = \Illuminate\Support\Facades\DB::table('application_settings')
                ->where('key', 'timezone')->value('value');
            if ($tz) {
                $tz = is_string($tz) && str_starts_with($tz, '"') ? json_decode($tz) : $tz;
                if ($tz && in_array($tz, timezone_identifiers_list())) {
                    config(['app.timezone' => $tz]);
                    date_default_timezone_set($tz);
                }
            }
        } catch (\Exception $e) {}

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(500)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(($request->input('email') ?: $request->ip()).'|'.$request->ip());
        });

        RateLimiter::for('pin', function (Request $request) {
            $id = $request->input('email') ?: $request->input('employee_number');
            return Limit::perMinute(10)->by(($id ?: 'guest').'|'.$request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('refund', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('assets', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
