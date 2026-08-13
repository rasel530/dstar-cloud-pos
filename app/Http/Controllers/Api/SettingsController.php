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
                ApplicationSetting::updateOrCreate(
                    ['tenant_id' => $tenantId, 'key' => $s['key']],
                    ['value' => $s['value'] ?? '']
                );
            }
            $settings = ApplicationSetting::where('tenant_id', $tenantId)
                ->orderBy('key')->get()->pluck('value', 'key');
            return response()->json(['data' => $settings]);
        }

        $value = $validated['value'] ?? '';
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
