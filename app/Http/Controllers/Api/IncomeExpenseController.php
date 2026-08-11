<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\IncomeExpenseCategory;
use App\Models\Document;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');

        $query = IncomeExpense::query()
            ->where('tenant_id', $tenantId)
            ->with(['category:id,name,color,type', 'user:id,first_name,last_name,username'])
            ->orderBy('date', 'desc')
            ->orderBy('reference_number', 'desc');

        if ($branchId && !SystemModeService::isSingleMode()) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', "%{$search}%")
                    ->orWhere('reference_number', 'ilike', "%{$search}%");
            });
        }

        $entries = $query->paginate($request->query('per_page', 20));

        return response()->json($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|uuid|exists:income_expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:50',
            'date' => 'required|date',
            'status' => 'nullable|string|max:20',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['user_id'] = auth()->id();
        $validated['reference_number'] = IncomeExpense::generateNumber($validated['type']);

        if (!SystemModeService::isSingleMode()) {
            $validated['branch_id'] = $request->header('X-Active-Branch');
        }

        $entry = IncomeExpense::create($validated);
        $entry->load(['category:id,name,color,type', 'user:id,first_name,last_name,username']);

        return response()->json(['data' => $entry], 201);
    }

    public function show(string $id): JsonResponse
    {
        $entry = $this->findEntry($id);
        $entry->load(['category:id,name,color,type', 'user:id,first_name,last_name,username', 'document']);
        return response()->json(['data' => $entry]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $entry = $this->findEntry($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|uuid|exists:income_expense_categories,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:50',
            'date' => 'sometimes|date',
            'status' => 'nullable|string|max:20',
        ]);

        $entry->update($validated);
        $entry->load(['category:id,name,color,type', 'user:id,first_name,last_name,username']);

        return response()->json(['data' => $entry]);
    }

    public function destroy(string $id): JsonResponse
    {
        $entry = $this->findEntry($id);
        $entry->delete();

        return response()->json(['message' => 'Entry deleted successfully.']);
    }

    public function syncPosSales(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $salesCategory = IncomeExpenseCategory::where('tenant_id', $tenantId)
            ->where('type', 'income')
            ->where('name', 'Sales Revenue')
            ->first();

        if (!$salesCategory) {
            return response()->json(['message' => 'Sales Revenue category not found.'], 422);
        }

        $salesByPayment = DB::table('payments')
            ->join('documents', 'payments.document_id', '=', 'documents.id')
            ->join('payment_types', 'payments.payment_type_id', '=', 'payment_types.id')
            ->where('payments.tenant_id', $tenantId)
            ->whereBetween('documents.date', [$dateFrom, $dateTo])
            ->where('documents.total', '>', 0)
            ->selectRaw("
                DATE(documents.date) as sale_date,
                payment_types.name as payment_method,
                SUM(payments.amount) as total_amount,
                COUNT(DISTINCT documents.id) as order_count
            ")
            ->groupBy(DB::raw('DATE(documents.date)'), 'payment_types.name')
            ->orderBy('sale_date')
            ->orderBy('payment_method')
            ->get();

        $synced = 0;
        foreach ($salesByPayment as $sale) {
            $match = [
                'tenant_id' => $tenantId,
                'type' => 'income',
                'category_id' => $salesCategory->id,
                'payment_method' => $sale->payment_method,
            ];
            $entry = IncomeExpense::where($match)
                ->whereDate('date', $sale->sale_date)
                ->where('description', 'like', 'Auto-synced: %')
                ->first();

            if ($entry) {
                $entry->update([
                    'amount' => $sale->total_amount,
                    'description' => 'Auto-synced: ' . $sale->order_count . ' order(s) on ' . $sale->sale_date,
                ]);
                $synced++;
            } else {
                IncomeExpense::create([
                    'tenant_id' => $tenantId,
                    'category_id' => $salesCategory->id,
                    'user_id' => auth()->id(),
                    'reference_number' => IncomeExpense::generateNumber('income'),
                    'type' => 'income',
                    'amount' => $sale->total_amount,
                    'description' => 'Auto-synced: ' . $sale->order_count . ' order(s) on ' . $sale->sale_date,
                    'payment_method' => $sale->payment_method,
                    'date' => $sale->sale_date,
                    'status' => 'completed',
                ]);
                $synced++;
            }
        }

        return response()->json([
            'message' => "{$synced} income entries synced from POS sales (by payment method).",
            'synced_count' => $synced,
        ]);
    }

    private function findEntry(string $id): IncomeExpense
    {
        return IncomeExpense::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);
    }
}
