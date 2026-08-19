<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use Illuminate\Http\Request;

class LogoController extends Controller
{
    public function appLogo(Request $request)
    {
        return $this->serve('logo', $request);
    }

    public function receiptLogo(Request $request)
    {
        return $this->serve('receipt_logo', $request);
    }

    private function serve(string $key, Request $request)
    {
        // Only raster image types may be served — SVG is rejected because it
        // can embed scripts that execute when opened directly.
        $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif'];

        $query = ApplicationSetting::where('key', $key);
        $logo = (clone $query)->whereNull('tenant_id')->value('value');

        if (! $logo && auth()->user()?->tenant_id) {
            $logo = (clone $query)->where('tenant_id', auth()->user()->tenant_id)->value('value');
        }

        // Single-company deployments keep the pre-auth login-page logo working.
        if (! $logo && \App\Models\Tenant::where('is_company', true)->count() <= 1) {
            $logo = (clone $query)->whereNotNull('tenant_id')->value('value');
        }

        if (! is_string($logo) || ! str_starts_with($logo, 'data:image')) {
            return response('', 204);
        }

        $mime = 'image/png';
        if (preg_match('/^data:(image\/[a-z+]+);base64,/', $logo, $m)) {
            $mime = $m[1];
        }

        if (! in_array($mime, $allowed, true)) {
            return response('', 204);
        }

        $pos = strpos($logo, 'base64,');
        $binary = $pos !== false ? base64_decode(substr($logo, $pos + 7)) : '';

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
