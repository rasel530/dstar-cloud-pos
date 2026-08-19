<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function getModuleIds(): array
    {
        return array_keys(config('modules.list', []));
    }

    private function getModuleLabels(): array
    {
        return array_map(fn($m) => $m['label'], config('modules.list', []));
    }

    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orWhereNull('tenant_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $modules = config('modules.list', []);

        $roles->each(function ($role) use ($modules) {
            $role->module_list = array_keys($modules);
            $role->module_labels = array_map(fn($m) => $m['label'], $modules);
        });

        return response()->json(['data' => $roles]);
    }

    public function store(Request $request): JsonResponse
    {
        $moduleIds = $this->getModuleIds();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'access_level' => 'required|integer|min:0|max:99',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', $moduleIds),
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $role = Role::create($validated);

        if (!empty($request->permissions)) {
            foreach ($request->permissions as $module) {
                RolePermission::create(['role_id' => $role->id, 'module' => $module]);
            }
        }

        $role->load('permissions');

        return response()->json(['data' => $role], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::where(function ($q) {
            $q->where('tenant_id', auth()->user()->tenant_id)->orWhereNull('tenant_id');
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'access_level' => 'sometimes|integer|min:0|max:99',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update($validated);

        if ($request->has('permissions')) {
            RolePermission::where('role_id', $role->id)->delete();
            foreach ($request->permissions as $module) {
                RolePermission::create(['role_id' => $role->id, 'module' => $module]);
            }
        }

        $role->load('permissions');

        return response()->json(['data' => $role]);
    }

    public function destroy($id): JsonResponse
    {
        $role = Role::where(function ($q) {
            $q->where('tenant_id', auth()->user()->tenant_id)->orWhereNull('tenant_id');
        })->findOrFail($id);
        if (in_array($role->access_level, [0, 5, 9]) && $role->tenant_id === null) {
            return response()->json(['message' => 'Default roles cannot be deleted.'], 422);
        }
        $role->delete();

        return response()->json(null, 204);
    }

    public function modules(): JsonResponse
    {
        return response()->json(['data' => config('modules.list', [])]);
    }
}
