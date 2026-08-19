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
            ->through(fn ($user) => $user->makeHidden(['password'])->setAttribute('pin_set', !is_null($user->pin_code)));

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = auth()->user();

        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'username'        => 'required|string|max:255|unique:users,username,NULL,id,deleted_at,NULL',
            'employee_number' => 'nullable|integer|min:1|max:65535|unique:users,employee_number,NULL,id,tenant_id,' . $actor->tenant_id,
            'email'           => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'        => 'required|string|min:8',
            'access_level'    => 'sometimes|integer|min:0',
            'is_enabled'      => 'sometimes|boolean',
            'can_edit_price'  => 'sometimes|boolean',
            'pin_code'        => 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'branch_id'       => 'nullable|exists:tenants,id',
            'branch_ids'      => 'nullable|array',
            'branch_ids.*'    => 'exists:tenants,id',
        ]);

        // No user may create another user above their own access level, and
        // levels outside the known set {0,5,9} are rejected.
        $level = $validated['access_level'] ?? 0;
        if (! in_array($level, [0, 5, 9], true) || $level > $actor->access_level) {
            return response()->json(['message' => 'Invalid access level.'], 422);
        }
        $validated['access_level'] = $level;

        $branchIds = $validated['branch_ids'] ?? [];
        unset($validated['branch_ids']);
        foreach (array_merge($branchIds, [$validated['branch_id'] ?? null]) as $bid) {
            if ($bid && ! $actor->canAccessBranch($bid)) {
                return response()->json(['message' => 'Invalid branch assignment.'], 422);
            }
        }

        $validated['tenant_id'] = $actor->tenant_id;
        $validated['password'] = bcrypt($validated['password']);

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
        $actor = auth()->user();
        $user = User::where('tenant_id', $actor->tenant_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'first_name'      => 'sometimes|string|max:255',
            'last_name'       => 'sometimes|string|max:255',
            'username'        => 'sometimes|string|max:255|unique:users,username,' . $id . ',id,deleted_at,NULL',
            'employee_number' => 'nullable|integer|min:1|max:65535|unique:users,employee_number,' . $id . ',id,tenant_id,' . $actor->tenant_id,
            'email'           => 'sometimes|email|unique:users,email,' . $id . ',id,deleted_at,NULL',
            'password'        => 'sometimes|string|min:8',
            'access_level'    => 'sometimes|integer|min:0',
            'is_enabled'      => 'sometimes|boolean',
            'can_edit_price'  => 'sometimes|boolean',
            'pin_code'        => 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'branch_id'       => 'nullable|exists:tenants,id',
            'branch_ids'      => 'nullable|array',
            'branch_ids.*'    => 'exists:tenants,id',
        ]);

        // Managers may not elevate users above their own level; only known levels allowed.
        if (array_key_exists('access_level', $validated)) {
            if (! in_array($validated['access_level'], [0, 5, 9], true)
                || $validated['access_level'] > $actor->access_level
                || ($validated['access_level'] > $user->access_level && $actor->access_level < 9)) {
                return response()->json(['message' => 'Invalid access level.'], 422);
            }
        }

        $branchIds = $validated['branch_ids'] ?? null;
        unset($validated['branch_ids']);
        foreach (array_merge($branchIds ?? [], [$validated['branch_id'] ?? null]) as $bid) {
            if ($bid && ! $actor->canAccessBranch($bid)) {
                return response()->json(['message' => 'Invalid branch assignment.'], 422);
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

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
            'access_level' => 'required|integer|min:0',
        ]);

        if (! in_array($validated['access_level'], [0, 5, 9], true)) {
            return response()->json(['message' => 'Invalid access level.'], 422);
        }

        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $user->update($validated);

        return response()->json([
            'message' => 'User access level updated successfully.',
            'user'    => $user->setHidden(['password']),
        ]);
    }
}
