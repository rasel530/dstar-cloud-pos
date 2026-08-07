<?php

namespace App\Console\Commands;

use App\Models\ZReport;
use App\Models\Document;
use App\Models\Payment;
use App\Models\StartingCash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateZReport extends Command
{
    protected $signature = 'pos:zreport {tenant_id?} {--user=}';
    protected $description = 'Generate a Z-report (end-of-day / shift close)';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');
        $userId = $this->option('user');

        if (! $tenantId) {
            $this->error('tenant_id is required as an argument.');
            return 1;
        }

        $lastZReport = ZReport::where('tenant_id', $tenantId)
            ->orderByDesc('number')
            ->first();

        $query = Document::where('tenant_id', $tenantId)
            ->where('is_clocked_out', false);

        if ($lastZReport) {
            $query->where('created_at', '>', $lastZReport->created_at);
        }

        $documents = $query->get();
        $documentIds = $documents->pluck('id');

        if ($documentIds->isEmpty()) {
            $this->info('No unclocked documents found since the last Z-report.');
            return 0;
        }

        $fromDocId = $documents->first()->id;
        $toDocId = $documents->last()->id;

        $totalSales = $documents->sum('total');
        $totalDiscount = $documents->sum('discount');
        $documentCount = $documents->count();

        $payments = Payment::where('tenant_id', $tenantId)
            ->whereIn('document_id', $documentIds)
            ->with('paymentType')
            ->get();

        $paymentBreakdown = $payments->groupBy('paymentType.name')
            ->map(fn ($group) => $group->sum('amount'))
            ->toArray();

        $reportNumber = ($lastZReport?->number ?? 0) + 1;

        DB::transaction(function () use ($tenantId, $reportNumber, $fromDocId, $toDocId, $documentIds, $totalSales, $totalDiscount, $documentCount, $paymentBreakdown, $userId) {

            $zReport = ZReport::create([
                'tenant_id' => $tenantId,
                'number' => $reportNumber,
                'from_document_id' => $fromDocId,
                'to_document_id' => $toDocId,
            ]);

            Document::whereIn('id', $documentIds)->update(['is_clocked_out' => true]);

            $this->info("Z-Report #{$reportNumber} generated.");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Documents', $documentCount],
                    ['Total Sales', '$' . number_format($totalSales, 2)],
                    ['Total Discount', '$' . number_format($totalDiscount, 2)],
                ]
            );

            if (! empty($paymentBreakdown)) {
                $this->info('Payment Breakdown:');
                foreach ($paymentBreakdown as $type => $amount) {
                    $this->line("  {$type}: \$" . number_format($amount, 2));
                }
            }

            if ($userId) {
                StartingCash::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'amount' => 0,
                    'description' => "Auto-created for Z-Report #{$reportNumber}",
                    'starting_cash_type' => 0,
                    'z_report_number' => $reportNumber,
                ]);
            }
        });

        return 0;
    }
}
