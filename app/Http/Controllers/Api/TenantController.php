<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        $tenants = Tenant::orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->when(auth()->user()->access_level < 9, fn($q) => $q->whereIn('id', auth()->user()->branches->pluck('id')))
            ->get();

        return response()->json(['data' => $tenants]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:50|unique:tenants,branch_code',
            'business_type' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'is_company' => 'boolean',
            'company_id' => 'nullable|exists:tenants,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_active'] = true;

        $tenant = Tenant::create($validated);

        return response()->json(['data' => $tenant], 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        return response()->json(['data' => $tenant]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'branch_code' => 'sometimes|nullable|string|max:50|unique:tenants,branch_code,' . $id,
            'business_type' => 'sometimes|required|string|max:50',
            'address' => 'sometimes|nullable|string|max:500',
            'phone' => 'sometimes|nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
            'is_company' => 'boolean',
            'company_id' => 'nullable|exists:tenants,id',
        ]);

        $tenant->update($validated);

        return response()->json(['data' => $tenant]);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        \App\Models\BranchInventory::where('branch_id', $id)->delete();
        \App\Models\StockTransfer::where('from_branch_id', $id)->orWhere('to_branch_id', $id)->delete();
        \App\Models\User::where('branch_id', $id)->update(['branch_id' => null]);
        \Illuminate\Support\Facades\DB::table('user_branches')->where('branch_id', $id)->delete();
        $tenant->delete();
        return response()->json(null, 204);
    }

    public function switch(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        session(['tenant_id' => $tenant->id, 'active_branch_id' => $tenant->id]);
        return response()->json(['data' => $tenant]);
    }
}
