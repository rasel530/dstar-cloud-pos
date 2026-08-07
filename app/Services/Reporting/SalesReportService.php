<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function salesSummary(string $tenantId, string $startDate, string $endDate): array
    {
        $sales = Document::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalSales = $sales->count();
        $grossRevenue = (float) $sales->sum('total');
        $totalDiscount = (float) $sales->sum('discount');
        $totalTax = 0;
        $netRevenue = $grossRevenue - $totalDiscount;
        $totalRefunds = (float) $sales->whereNotNull('reference_document_number')->sum('total');

        $averageOrderValue = $totalSales > 0 ? $netRevenue / $totalSales : 0.0;
        $totalOrders = $sales->whereNull('reference_document_number')->count();

        $salesByDay = Document::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'gross_revenue' => $grossRevenue,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'net_revenue' => $netRevenue,
            'total_refunds' => $totalRefunds,
            'average_order_value' => round($averageOrderValue, 2),
            'sales_by_day' => $salesByDay,
        ];
    }

    public function bestSellingItems(
        string $tenantId,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 20
    ): array {
        $activeBranchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $query = DocumentItem::select(
                'document_items.product_id',
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(document_items.quantity) as total_quantity'),
                DB::raw('SUM(document_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT document_items.document_id) as order_count')
            )
            ->join('products', 'document_items.product_id', '=', 'products.id')
            ->join('documents', 'document_items.document_id', '=', 'documents.id')
            ->where('documents.tenant_id', $tenantId)
            ->whereNull('documents.reference_document_number')
            ->groupBy('document_items.product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->limit($limit);

        if ($startDate && $endDate) {
            $query->whereBetween('documents.created_at', [$startDate, $endDate]);
        }

        return $query->get()->toArray();
    }

    public function paymentTypeBreakdown(string $tenantId, string $startDate, string $endDate): array
    {
        $payments = Payment::select(
                'payment_types.name as payment_type',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(payments.amount) as total_amount'),
                DB::raw('AVG(payments.amount) as average_amount')
            )
            ->join('payment_types', 'payments.payment_type_id', '=', 'payment_types.id')
            ->where('payments.tenant_id', $tenantId)
            ->whereBetween('payments.date', [$startDate, $endDate])
            ->groupBy('payment_types.id', 'payment_types.name')
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();

        $grandTotal = (float) array_sum(array_column($payments, 'total_amount'));

        foreach ($payments as &$payment) {
            $payment['percentage'] = $grandTotal > 0
                ? round(((float) $payment['total_amount'] / $grandTotal) * 100, 2)
                : 0.0;
        }
        unset($payment);

        return [
            'grand_total' => $grandTotal,
            'payment_types' => $payments,
        ];
    }

    public function employeeSales(string $tenantId, string $startDate, string $endDate): array
    {
        $sales = Document::select(
                'documents.user_id',
                'users.name as employee_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(documents.total) as total_revenue'),
                DB::raw('SUM(documents.discount) as total_discounts'),
                DB::raw('AVG(documents.total) as average_order_value')
            )
            ->join('users', 'documents.user_id', '=', 'users.id')
            ->where('documents.tenant_id', $tenantId)
            ->whereNull('documents.reference_document_number')
            ->whereBetween('documents.date', [$startDate, $endDate])
            ->groupBy('documents.user_id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();

        $totals = (object) [
            'orders' => array_sum(array_column($sales, 'total_orders')),
            'revenue' => array_sum(array_column($sales, 'total_revenue')),
        ];

        return [
            'employees' => $sales,
            'total_orders' => $totals->orders,
            'total_revenue' => $totals->revenue,
        ];
    }
}
