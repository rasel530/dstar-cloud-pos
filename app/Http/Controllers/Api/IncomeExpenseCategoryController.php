<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeExpenseCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $query = IncomeExpenseCategory::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('rank')
            ->orderBy('name');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $categories = $query->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'rank' => 'nullable|integer',
            'is_enabled' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $category = IncomeExpenseCategory::create($validated);

        return response()->json(['data' => $category], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->findCategory($id);
        return response()->json(['data' => $category]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = $this->findCategory($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|in:income,expense',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'rank' => 'nullable|integer',
            'is_enabled' => 'nullable|boolean',
        ]);

        $category->update($validated);

        return response()->json(['data' => $category]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = $this->findCategory($id);

        if (IncomeExpenseCategory::where('id', $id)->whereHas('entries')->exists()) {
            return response()->json(['message' => 'Cannot delete category with existing entries.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    private function findCategory(string $id): IncomeExpenseCategory
    {
        return IncomeExpenseCategory::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);
    }
}
