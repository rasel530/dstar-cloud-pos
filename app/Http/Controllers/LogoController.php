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
        $query = ApplicationSetting::where('key', $key);
        $logo = (clone $query)->whereNull('tenant_id')->value('value') ?? (clone $query)->value('value');

        if (!$logo || !str_starts_with($logo, 'data:image')) {
            return response('', 204);
        }

        $mime = 'image/png';
        if (preg_match('/^data:(image\/[a-z+]+);base64,/', $logo, $m)) {
            $mime = $m[1];
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
