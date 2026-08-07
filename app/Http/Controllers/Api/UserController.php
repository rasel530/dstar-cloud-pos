<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessLevel;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('branches:id,name,branch_code')
            ->orderBy('first_name')
            ->paginate(25)
            ->through(fn ($user) => $user->makeHidden(['password']));

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,NULL,id,deleted_at,NULL',
            'email'        => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'     => 'required|string|min:8',
            'access_level' => 'sometimes|integer|min:0|max:99',
            'is_enabled'   => 'sometimes|boolean',
            'pin_code'     => 'nullable|string|max:6',
            'branch_id'    => 'nullable|exists:tenants,id',
            'branch_ids'   => 'nullable|array',
            'branch_ids.*' => 'exists:tenants,id',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['password'] = bcrypt($validated['password']);

        $branchIds = $validated['branch_ids'] ?? [];
        unset($validated['branch_ids']);

        $user = User::create($validated);
        $user->branches()->sync($branchIds);

        return response()->json(['data' => $user->makeHidden(['password'])], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'user' => $user->setHidden(['password']),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'username'     => 'sometimes|string|max:255|unique:users,username,' . $id . ',id,deleted_at,NULL',
            'email'        => 'sometimes|email|unique:users,email,' . $id . ',id,deleted_at,NULL',
            'password'     => 'sometimes|string|min:8',
            'access_level' => 'sometimes|integer|min:0|max:99',
            'is_enabled'   => 'sometimes|boolean',
            'pin_code'     => 'nullable|string|max:6',
            'branch_id'    => 'nullable|exists:tenants,id',
            'branch_ids'   => 'nullable|array',
            'branch_ids.*' => 'exists:tenants,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $branchIds = $validated['branch_ids'] ?? null;
        unset($validated['branch_ids']);

        $user->update($validated);
        if ($branchIds !== null) {
            $user->branches()->sync($branchIds);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => $user->setHidden(['password']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function updateAccessLevel(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'access_level' => 'required|integer|min:0|max:99',
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $user->update($validated);

        return response()->json([
            'message' => 'User access level updated successfully.',
            'user'    => $user->setHidden(['password']),
        ]);
    }
}
