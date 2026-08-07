<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
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

        return response()->json([
            'data' => [
                'user'  => $user,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        session()->flush();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user->is_enabled) {
            return response()->json(['message' => 'Account is disabled.'], 403);
        }

        $user->load('branches:id,name,branch_code');

        $systemMode = \App\Services\SystemModeService::getMode();
        $user->setAttribute('system_mode', $systemMode);

        return response()->json([
            'data' => $user,
        ]);
    }

    public function pinLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'pin_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_enabled', true)
            ->first();

        if (!$user || !Hash::check($request->pin_code, $user->pin_code)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user'  => $user,
                'token' => $token,
            ],
        ]);
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string',
            'new_pin'     => 'required|string|min:4|max:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_pin, $user->pin_code)) {
            return response()->json(['message' => 'Current PIN is incorrect'], 422);
        }

        $user->pin_code = $request->new_pin;
        $user->save();

        return response()->json(['message' => 'PIN changed successfully']);
    }
}
