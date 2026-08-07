<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::where('tenant_id', auth()->user()->tenant_id)->paginate(25);

        return response()->json($companies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $company = Company::create($validated);

        return response()->json([
            'message' => 'Company created successfully.',
            'company' => $company,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $company = Company::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        return response()->json([
            'company' => $company,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $company = Company::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
        ]);

        $company->update($validated);

        return response()->json([
            'message' => 'Company updated successfully.',
            'company' => $company,
        ]);
    }
}
