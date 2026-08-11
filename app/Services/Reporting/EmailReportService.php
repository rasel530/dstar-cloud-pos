<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Models\Tenant;
use App\Models\ApplicationSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class EmailReportService
{
    protected SalesReportService $salesReportService;

    public function __construct(SalesReportService $salesReportService)
    {
        $this->salesReportService = $salesReportService;
    }

    public function sendDailyReport(string $tenantId): void
    {
        $today = Carbon::now()->toDateString();
        $startOfDay = Carbon::now()->startOfDay()->toDateTimeString();
        $endOfDay = Carbon::now()->endOfDay()->toDateTimeString();

        $summary = $this->salesReportService->salesSummary($tenantId, $startOfDay, $endOfDay);
        $bestSellers = $this->salesReportService->bestSellingItems($tenantId, $startOfDay, $endOfDay, 10);
        $paymentBreakdown = $this->salesReportService->paymentTypeBreakdown($tenantId, $startOfDay, $endOfDay);

        $tenant = Tenant::find($tenantId);
        $tenantName = $tenant ? $tenant->name : 'Your Store';

        $companySetting = ApplicationSetting::where('tenant_id', $tenantId)
            ->where('key', 'company_name')
            ->first();
        $companyName = $companySetting?->value ?: config('app.name', 'POS System');

        $reportData = [
            'tenant_name' => $tenantName,
            'report_date' => $today,
            'summary' => $summary,
            'best_sellers' => $bestSellers,
            'payment_breakdown' => $paymentBreakdown,
        ];

        $recipientEmails = $this->getReportRecipients($tenantId);

        foreach ($recipientEmails as $email) {
            $this->sendToEmail($email, $reportData);
        }
    }

    public function sendToEmail(string $email, array $reportData): void
    {
        Mail::send([], [], function ($message) use ($email, $reportData) {
            $subject = sprintf(
                'Daily Sales Report - %s | %s',
                $reportData['tenant_name'],
                $reportData['report_date']
            );

            $message->to($email)
                ->subject($subject)
                ->html($this->buildReportHtml($reportData));
        });
    }

    protected function getReportRecipients(string $tenantId): array
    {
        $setting = ApplicationSetting::where('tenant_id', $tenantId)
            ->where('key', 'report_email_recipients')
            ->first();

        if ($setting && !empty($setting->value)) {
            $emails = array_map('trim', explode(',', $setting->value));

            return array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            });
        }

        $tenant = Tenant::find($tenantId);

        if ($tenant && !empty($tenant->email)) {
            return [$tenant->email];
        }

        return [];
    }

    protected function buildReportHtml(array $data): string
    {
        $summary = $data['summary'];
        $bestSellers = $data['best_sellers'];
        $paymentBreakdown = $data['payment_breakdown'];

        $bestSellerRows = '';
        foreach ($bestSellers as $index => $item) {
            $rank = $index + 1;
            $bestSellerRows .= sprintf(
                '<tr><td>%d</td><td>%s</td><td>%s</td><td>%.0f</td><td>%.2f</td></tr>',
                $rank,
                htmlspecialchars($item['product_name']),
                htmlspecialchars($item['sku'] ?? 'N/A'),
                $item['total_quantity'],
                $item['total_revenue']
            );
        }

        $paymentRows = '';
        foreach ($paymentBreakdown['payment_types'] as $pt) {
            $paymentRows .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%.2f</td><td>%.1f%%</td></tr>',
                htmlspecialchars(ucfirst($pt['payment_type'])),
                $pt['transaction_count'],
                $pt['total_amount'],
                $pt['percentage']
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h2 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary td:first-child { font-weight: bold; width: 40%; }
        .footer { font-size: 12px; color: #777; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$data['tenant_name']} - Daily Sales Report</h1>
        <p>Report Date: {$data['report_date']}</p>

        <h2>Sales Summary</h2>
        <table class="summary">
            <tr><td>Total Orders</td><td>{$summary['total_orders']}</td></tr>
            <tr><td>Gross Revenue</td><td>{$summary['gross_revenue']}</td></tr>
            <tr><td>Total Discounts</td><td>{$summary['total_discount']}</td></tr>
            <tr><td>Total Tax</td><td>{$summary['total_tax']}</td></tr>
            <tr><td>Net Revenue</td><td>{$summary['net_revenue']}</td></tr>
            <tr><td>Average Order Value</td><td>{$summary['average_order_value']}</td></tr>
        </table>

        <h2>Best Selling Items</h2>
        <table>
            <tr><th>#</th><th>Product</th><th>SKU</th><th>Qty Sold</th><th>Revenue</th></tr>
            {$bestSellerRows}
        </table>

        <h2>Payment Breakdown</h2>
        <table>
            <tr><th>Type</th><th>Transactions</th><th>Amount</th><th>Share</th></tr>
            {$paymentRows}
        </table>

        <div class="footer">
            Generated by {$companyName} — {$data['report_date']}
        </div>
    </div>
</body>
</html>
HTML;
    }
}
