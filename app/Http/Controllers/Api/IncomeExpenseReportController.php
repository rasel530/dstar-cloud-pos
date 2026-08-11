<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeExpenseReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $query = IncomeExpense::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled');

        if ($branchId && !SystemModeService::isSingleMode()) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }

        $summary = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
            COUNT(*) as total_count
        ")->first();

        $topIncomeCategories = DB::table('income_expenses')
            ->join('income_expense_categories', 'income_expenses.category_id', '=', 'income_expense_categories.id')
            ->where('income_expenses.tenant_id', $tenantId)
            ->where('income_expenses.type', 'income')
            ->whereBetween('income_expenses.date', [$dateFrom, $dateTo])
            ->where('income_expenses.status', '!=', 'cancelled')
            ->selectRaw("
                income_expense_categories.id,
                income_expense_categories.name,
                income_expense_categories.color,
                SUM(income_expenses.amount) as total_amount,
                COUNT(*) as entry_count
            ")
            ->groupBy('income_expense_categories.id', 'income_expense_categories.name', 'income_expense_categories.color')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $topExpenseCategories = DB::table('income_expenses')
            ->join('income_expense_categories', 'income_expenses.category_id', '=', 'income_expense_categories.id')
            ->where('income_expenses.tenant_id', $tenantId)
            ->where('income_expenses.type', 'expense')
            ->whereBetween('income_expenses.date', [$dateFrom, $dateTo])
            ->where('income_expenses.status', '!=', 'cancelled')
            ->selectRaw("
                income_expense_categories.id,
                income_expense_categories.name,
                income_expense_categories.color,
                SUM(income_expenses.amount) as total_amount,
                COUNT(*) as entry_count
            ")
            ->groupBy('income_expense_categories.id', 'income_expense_categories.name', 'income_expense_categories.color')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $recentEntries = IncomeExpense::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['category:id,name,color,type'])
            ->orderBy('date', 'desc')
            ->orderBy('reference_number', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['data' => [
            'summary' => $summary,
            'top_income_categories' => $topIncomeCategories,
            'top_expense_categories' => $topExpenseCategories,
            'recent_entries' => $recentEntries,
        ]]);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->query('type', 'expense');
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $data = DB::table('income_expenses')
            ->join('income_expense_categories', 'income_expenses.category_id', '=', 'income_expense_categories.id')
            ->where('income_expenses.tenant_id', $tenantId)
            ->where('income_expenses.type', $type)
            ->whereBetween('income_expenses.date', [$dateFrom, $dateTo])
            ->where('income_expenses.status', '!=', 'cancelled')
            ->selectRaw("
                income_expense_categories.id,
                income_expense_categories.name,
                income_expense_categories.color,
                SUM(income_expenses.amount) as total_amount,
                COUNT(*) as entry_count
            ")
            ->groupBy('income_expense_categories.id', 'income_expense_categories.name', 'income_expense_categories.color')
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = $data->sum('total_amount');

        $data = $data->map(function ($item) use ($grandTotal) {
            $item->percentage = $grandTotal > 0 ? round(($item->total_amount / $grandTotal) * 100, 1) : 0;
            return $item;
        });

        return response()->json(['data' => $data, 'grand_total' => $grandTotal]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $year = $request->query('year', now()->year);

        $data = DB::table('income_expenses')
            ->where('tenant_id', $tenantId)
            ->whereYear('date', $year)
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                TO_CHAR(date, 'YYYY-MM') as month,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income_total,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense_total
            ")
            ->groupByRaw("TO_CHAR(date, 'YYYY-MM')")
            ->orderBy('month')
            ->get();

        return response()->json(['data' => $data]);
    }
}
