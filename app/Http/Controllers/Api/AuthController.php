<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const PIN_VALIDATION_REGEX = '/^\d{4}$/';

    private const BLOCKED_PINS = [
        '0000', '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999',
        '1234', '2345', '3456', '4567', '5678', '6789',
        '4321', '5432', '6543', '7654', '8765', '9876',
        '1212', '1004', '2000', '6969',
    ];

    private function validatePinStrength(string $pin): bool
    {
        if (!preg_match(self::PIN_VALIDATION_REGEX, $pin)) {
            return false;
        }

        if (in_array($pin, self::BLOCKED_PINS, true)) {
            return false;
        }

        return true;
    }

    private function getPinLockoutDuration(int $attempts): int
    {
        return match (true) {
            $attempts >= 15 => 1440,
            $attempts >= 10 => 60,
            $attempts >= 5  => 15,
            default         => 0,
        };
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $request->user();

        if (!$user->is_enabled) {
            Auth::logout();
            return response()->json(['message' => 'Account is disabled. Contact administrator.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $this->logActivity($user, $request, 'login', 'Logged in');

        return response()->json([
            'data' => [
                'user'  => $user->setAttribute('has_pin', !is_null($user->pin_code)),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        session()->flush();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_enabled) {
            return response()->json(['message' => 'Account is disabled.'], 403);
        }

        $user->load('branches:id,name,branch_code');

        $systemMode = \App\Services\SystemModeService::getMode();
        $user->setAttribute('system_mode', $systemMode);
        $user->setAttribute('has_pin', !is_null($user->pin_code));

        return response()->json([
            'data' => $user,
        ]);
    }

    public function pinLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'pin_code' => ['required', 'string', 'regex:' . self::PIN_VALIDATION_REGEX],
        ]);

        $user = User::where('email', $request->email)
            ->where('is_enabled', true)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (is_null($user->pin_code)) {
            return response()->json(['message' => 'PIN not set up. Please use password to login.'], 401);
        }

        if ($user->pin_locked_until && $user->pin_locked_until->isFuture()) {
            $remaining = (int) ceil(now()->diffInMinutes($user->pin_locked_until));
            return response()->json([
                'message' => "Account locked due to too many failed PIN attempts. Try again in {$remaining} minutes, or use password instead.",
                'locked'  => true,
                'locked_until' => $user->pin_locked_until->toIso8601String(),
            ], 423);
        }

        if (Hash::check($request->pin_code, $user->pin_code)) {
            $user->update([
                'pin_attempts'     => 0,
                'pin_locked_until' => null,
                'last_login_at'    => now(),
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            $this->logActivity($user, $request, 'login', 'Logged in via PIN');

            return response()->json([
                'data' => [
                    'user'  => $user,
                    'token' => $token,
                ],
            ]);
        }

        $user->increment('pin_attempts');
        $user->refresh();

        $duration = $this->getPinLockoutDuration($user->pin_attempts);
        $remainingAttempts = max(0, 5 - $user->pin_attempts);

        if ($duration > 0) {
            $user->update(['pin_locked_until' => now()->addMinutes($duration)]);

            return response()->json([
                'message' => "Too many failed attempts. Account locked for {$duration} minutes. Use password to login.",
                'locked'  => true,
            ], 423);
        }

        return response()->json([
            'message' => "Invalid PIN. {$remainingAttempts} attempt" . ($remainingAttempts !== 1 ? 's' : '') . " remaining.",
        ], 401);
    }

    public function setupPin(Request $request): JsonResponse
    {
        $request->validate([
            'password'           => 'required|string',
            'pin'                => ['required', 'string', 'regex:' . self::PIN_VALIDATION_REGEX, 'not_in:' . implode(',', self::BLOCKED_PINS)],
            'pin_confirmation'   => 'required|string|same:pin',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password is incorrect'], 422);
        }

        if (!$this->validatePinStrength($request->pin)) {
            return response()->json(['message' => 'PIN is too weak. Avoid repeated digits (0000) or sequences (1234).'], 422);
        }

        $user->update([
            'pin_code'          => $request->pin,
            'pin_attempts'      => 0,
            'pin_locked_until'  => null,
        ]);

        return response()->json(['message' => 'PIN set up successfully']);
    }

    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'current_pin'        => 'required_without:password|string',
            'password'           => 'required_without:current_pin|string',
            'new_pin'            => ['required', 'string', 'regex:' . self::PIN_VALIDATION_REGEX, 'not_in:' . implode(',', self::BLOCKED_PINS)],
            'new_pin_confirmation' => 'required|string|same:new_pin',
        ]);

        $user = $request->user();

        if ($request->filled('current_pin')) {
            if (is_null($user->pin_code)) {
                return response()->json(['message' => 'No PIN is currently set. Use password to set your first PIN.'], 422);
            }

            if (!Hash::check($request->current_pin, $user->pin_code)) {
                return response()->json(['message' => 'Current PIN is incorrect'], 422);
            }
        } elseif ($request->filled('password')) {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Password is incorrect'], 422);
            }
        }

        if (!$this->validatePinStrength($request->new_pin)) {
            return response()->json(['message' => 'PIN is too weak. Avoid repeated digits (0000) or sequences (1234).'], 422);
        }

        $user->update([
            'pin_code'          => $request->new_pin,
            'pin_attempts'      => 0,
            'pin_locked_until'  => null,
        ]);

        return response()->json(['message' => 'PIN changed successfully']);
    }

    public function employeePinLogin(Request $request): JsonResponse
    {
        $request->validate([
            'employee_number' => 'required|integer|min:1',
            'pin_code'        => ['required', 'string', 'regex:' . self::PIN_VALIDATION_REGEX],
        ]);

        $user = User::where('employee_number', $request->employee_number)
            ->where('is_enabled', true)
            ->whereNotNull('pin_code')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->pin_locked_until && $user->pin_locked_until->isFuture()) {
            $remaining = (int) ceil(now()->diffInMinutes($user->pin_locked_until));
            return response()->json([
                'message'    => "Account locked. Try again in {$remaining} minutes.",
                'locked'     => true,
                'locked_until' => $user->pin_locked_until->toIso8601String(),
            ], 423);
        }

        if (Hash::check($request->pin_code, $user->pin_code)) {
            $user->update([
                'pin_attempts'     => 0,
                'pin_locked_until' => null,
                'last_login_at'    => now(),
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            $this->logActivity($user, $request, 'login', 'Logged in via employee PIN');

            return response()->json([
                'data' => [
                    'user'  => $user->setAttribute('has_pin', !is_null($user->pin_code)),
                    'token' => $token,
                ],
            ]);
        }

        $user->increment('pin_attempts');
        $user->refresh();

        $duration = $this->getPinLockoutDuration($user->pin_attempts);
        $remainingAttempts = max(0, 5 - $user->pin_attempts);

        if ($duration > 0) {
            $user->update(['pin_locked_until' => now()->addMinutes($duration)]);
            return response()->json([
                'message' => "Too many failed attempts. Account locked for {$duration} minutes.",
                'locked'  => true,
            ], 423);
        }

        return response()->json([
            'message' => "Invalid PIN. {$remainingAttempts} attempt" . ($remainingAttempts !== 1 ? 's' : '') . " remaining.",
        ], 401);
    }

    public function resetPin(string $userId): JsonResponse
    {
        $adminUser = request()->user();

        if ($adminUser->access_level < 5) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $adminTenantId = $adminUser->tenant_id;

        $user = User::where('tenant_id', $adminTenantId)
            ->where('id', $userId)
            ->firstOrFail();

        $user->update([
            'pin_code'          => null,
            'pin_attempts'      => 0,
            'pin_locked_until'  => null,
        ]);

        return response()->json(['message' => 'PIN has been reset. User must set up a new PIN on next login.']);
    }

    private function logActivity($user, Request $request, string $event, string $action): void
    {
        try {
            \App\Models\UserActivityLog::create([
                'user_id'    => $user->id,
                'tenant_id'  => $user->tenant_id,
                'branch_id'  => $request->header('X-Active-Branch'),
                'module'     => 'Security',
                'action'     => $action,
                'event'      => $event,
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'ip_address' => $request->ip(),
                'device'     => $this->detectDevice($request),
            ]);
        } catch (\Exception $e) { /* activity log is non-critical */ }
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
}
