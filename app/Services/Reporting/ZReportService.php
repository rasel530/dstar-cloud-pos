<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Models\ZReport;
use App\Models\PaymentType;
use App\Models\Document;
use App\Models\Payment;
use Carbon\Carbon;

class ZReportService
{
    protected SalesReportService $salesReportService;

    public function __construct(SalesReportService $salesReportService)
    {
        $this->salesReportService = $salesReportService;
    }

    public function generate(string $tenantId, string $userId): array
    {
        $previousReport = $this->getPreviousZReport($tenantId);

        $startDate = $previousReport
            ? $previousReport['closed_at']
            : Carbon::now()->startOfDay()->toDateTimeString();

        $endDate = Carbon::now()->toDateTimeString();

        $salesQuery = Document::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalSalesCount = $salesQuery->count();
        $totalRevenue = (float) $salesQuery->sum('total');
        $totalDiscount = (float) $salesQuery->sum('discount');
        $totalTax = 0;

        $orders = \App\Models\PosOrder::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        foreach ($orders as $o) {
            $calc = (new \App\Services\Pricing\TaxCalculator)->calculate($o);
            $totalTax += $calc['tax'];
        }

        $totalRefunds = (float) $salesQuery->whereNotNull('reference_document_number')->sum('total');

        $payments = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payments.created_at', [$startDate, $endDate]);

        $totalAmount = function (string $typeName) use ($payments) {
            return (float) (clone $payments)
                ->join('payment_types', 'payments.payment_type_id', '=', 'payment_types.id')
                ->where('payment_types.name', $typeName)
                ->sum('payments.amount');
        };

        $totalCash = $totalAmount('cash');
        $totalCard = $totalAmount('card');
        $totalDigital = $totalAmount('digital_wallet');
        $totalBankTransfer = $totalAmount('bank_transfer');

        $paymentBreakdown = [
            'cash' => $totalCash,
            'card' => $totalCard,
            'digital_wallet' => $totalDigital,
            'bank_transfer' => $totalBankTransfer,
        ];

        $netRevenue = $totalRevenue - $totalRefunds;

        $reportData = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'report_date' => Carbon::now()->toDateString(),
            'period_from' => $startDate,
            'period_to' => $endDate,
            'starting_report_id' => $previousReport['id'] ?? null,
        ];

        $zReport = ZReport::create(array_merge($reportData, [
            'total_sales' => $totalSalesCount,
            'gross_revenue' => $totalRevenue,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_refunds' => $totalRefunds,
            'net_revenue' => $netRevenue,
            'total_cash' => $totalCash,
            'total_card' => $totalCard,
            'total_digital_wallet' => $totalDigital,
            'total_bank_transfer' => $totalBankTransfer,
            'payment_breakdown' => json_encode($paymentBreakdown),
            'closed_at' => Carbon::now(),
        ]));

        return [
            'report_id' => $zReport->id,
            'date' => $zReport->report_date,
            'period_from' => $zReport->period_from,
            'period_to' => $zReport->period_to,
            'total_sales' => $totalSalesCount,
            'gross_revenue' => $totalRevenue,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_refunds' => $totalRefunds,
            'net_revenue' => $netRevenue,
            'payment_breakdown' => $paymentBreakdown,
        ];
    }

    public function getPreviousZReport(string $tenantId): ?array
    {
        $report = ZReport::where('tenant_id', $tenantId)
            ->orderByDesc('closed_at')
            ->first();

        if (!$report) {
            return null;
        }

        return [
            'id' => $report->id,
            'closed_at' => $report->closed_at,
            'report_date' => $report->report_date,
            'net_revenue' => $report->net_revenue,
        ];
    }
}
