<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $globalSettings = ApplicationSetting::whereNull('tenant_id')
            ->orderBy('key')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $tenantSettings = ApplicationSetting::where('tenant_id', $tenantId)
            ->orderBy('key')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $settings = array_merge($globalSettings, $tenantSettings);

        // Logo data URLs bloat this endpoint (~180KB). Serve the logos as cached
        // assets instead and expose their URLs.
        $hasLogo = !empty($settings['logo'] ?? '') && str_starts_with($settings['logo'] ?? '', 'data:image');
        $hasReceiptLogo = !empty($settings['receipt_logo'] ?? '') && str_starts_with($settings['receipt_logo'] ?? '', 'data:image');
        unset($settings['logo'], $settings['logo_preview'], $settings['receipt_logo'], $settings['receipt_logo_preview']);
        $settings['logo_url'] = $hasLogo ? '/logo' : '';
        $settings['receipt_logo_url'] = $hasReceiptLogo ? '/receipt-logo' : '';

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required_without:settings|string|max:255',
            'value' => 'nullable',
            'settings' => 'required_without:key|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'nullable',
        ]);

        $tenantId = auth()->user()->tenant_id;

        if (isset($validated['settings'])) {
            foreach ($validated['settings'] as $s) {
                $safeKey = $s['key'];
                $safeValue = $this->sanitizeSetting($safeKey, $s['value'] ?? '');
                ApplicationSetting::updateOrCreate(
                    ['tenant_id' => $tenantId, 'key' => $safeKey],
                    ['value' => $safeValue]
                );
            }
            $settings = ApplicationSetting::where('tenant_id', $tenantId)
                ->orderBy('key')->get()->pluck('value', 'key');
            return response()->json(['data' => $settings]);
        }

        $value = $this->sanitizeSetting($validated['key'], $validated['value'] ?? '');
        $setting = ApplicationSetting::where('tenant_id', $tenantId)
            ->where('key', $validated['key'])
            ->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            ApplicationSetting::create(['tenant_id' => $tenantId, 'key' => $validated['key'], 'value' => $value]);
        }

        return response()->json(['data' => $setting]);
    }

    /**
     * Logo keys accept only safe raster data-URLs (no SVG, no arbitrary HTML).
     * Anything else is stored as an empty value.
     */
    private function sanitizeSetting(string $key, mixed $value): mixed
    {
        if (in_array($key, ['logo', 'receipt_logo', 'logo_preview', 'receipt_logo_preview'], true)) {
            if (is_string($value) && preg_match('/^data:image\/(png|jpeg|jpg|webp|gif);base64,/', $value)) {
                $pos = strpos($value, 'base64,');
                $binary = base64_decode(substr($value, $pos + 7));
                if ($binary !== false && strlen($binary) <= 3 * 1024 * 1024) {
                    return $value;
                }
            }
            return '';
        }
        return $value;
    }

    public function getByKey(Request $request, $key): JsonResponse
    {
        $setting = ApplicationSetting::where('tenant_id', auth()->user()->tenant_id)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return response()->json(['message' => 'Setting not found.'], 404);
        }

        return response()->json(['data' => $setting->value]);
    }
}
