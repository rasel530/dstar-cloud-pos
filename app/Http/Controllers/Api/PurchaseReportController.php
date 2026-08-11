<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');
        $dateFrom = $request->query('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $query = Purchase::query()->where('tenant_id', $tenantId)
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled');

        if ($branchId && !SystemModeService::isSingleMode()) {
            $query->where('branch_id', $branchId);
        }

        $summary = (clone $query)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(grand_total), 0) as total_amount,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due
        ")->first();

        $byStatus = DB::table('purchases')
            ->where('tenant_id', $tenantId)
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->when($branchId && !SystemModeService::isSingleMode(), fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total")
            ->groupBy('status')
            ->get();

        $topSuppliers = DB::table('purchases')
            ->join('customers', 'purchases.supplier_id', '=', 'customers.id')
            ->where('purchases.tenant_id', $tenantId)
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->where('purchases.status', '!=', 'cancelled')
            ->when($branchId && !SystemModeService::isSingleMode(), fn($q) => $q->where('purchases.branch_id', $branchId))
            ->selectRaw("customers.name, COUNT(*) as purchase_count, SUM(purchases.grand_total) as total_amount")
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return response()->json(['data' => [
            'summary'       => $summary,
            'by_status'     => $byStatus,
            'top_suppliers' => $topSuppliers,
        ]]);
    }

    public function bySupplier(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->query('date_from', now()->subMonths(3)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $data = DB::table('purchases')
            ->join('customers', 'purchases.supplier_id', '=', 'customers.id')
            ->where('purchases.tenant_id', $tenantId)
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->where('purchases.status', '!=', 'cancelled')
            ->selectRaw("
                customers.id as supplier_id,
                customers.name as supplier_name,
                customers.code as supplier_code,
                COUNT(*) as purchase_count,
                SUM(purchases.grand_total) as total_amount,
                AVG(purchases.grand_total) as avg_order_value
            ")
            ->groupBy('customers.id', 'customers.name', 'customers.code')
            ->orderByDesc('total_amount')
            ->paginate(25);

        return response()->json($data);
    }

    public function byProduct(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->query('date_from', now()->subMonths(3)->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $data = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->where('purchases.tenant_id', $tenantId)
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->where('purchases.status', '!=', 'cancelled')
            ->selectRaw("
                products.id as product_id,
                products.name as product_name,
                products.code as product_code,
                SUM(purchase_items.received_quantity) as total_quantity,
                SUM(purchase_items.total) as total_cost,
                AVG(purchase_items.unit_cost) as avg_unit_cost
            ")
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_cost')
            ->paginate(25);

        return response()->json($data);
    }

    public function monthly(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $months = (int) $request->query('months', 12);

        $data = DB::table('purchases')
            ->where('tenant_id', $tenantId)
            ->where('purchase_date', '>=', now()->subMonths($months)->startOfMonth())
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                TO_CHAR(purchase_date, 'YYYY-MM') as month,
                COUNT(*) as purchase_count,
                SUM(grand_total) as total_amount
            ")
            ->groupByRaw("TO_CHAR(purchase_date, 'YYYY-MM')")
            ->orderBy('month')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function outstandingPayments(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $data = Purchase::query()
            ->where('tenant_id', $tenantId)
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with(['supplier:id,name,code'])
            ->orderBy('purchase_date', 'desc')
            ->paginate(25);

        return response()->json($data);
    }
}
