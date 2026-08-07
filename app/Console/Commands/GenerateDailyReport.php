<?php

namespace App\Console\Commands;

use App\Services\Reporting\EmailReportService;
use Illuminate\Console\Command;

class GenerateDailyReport extends Command
{
    protected $signature = 'pos:daily-report {tenant_id?} {--email=}';
    protected $description = 'Generate and send the daily sales report';

    public function __construct(
        protected EmailReportService $emailReportService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        if (! $tenantId) {
            $this->error('tenant_id is required.');
            return 1;
        }

        $this->info("Generating daily report for tenant {$tenantId}...");

        try {
            $this->emailReportService->sendDailyReport($tenantId);
        } catch (\Throwable $e) {
            $this->error("Failed to generate or send daily report: {$e->getMessage()}");
            return 1;
        }

        $emailOverride = $this->option('email');
        if ($emailOverride) {
            if (! filter_var($emailOverride, FILTER_VALIDATE_EMAIL)) {
                $this->error("Invalid email address: {$emailOverride}");
                return 1;
            }
            $this->info("Overriding recipients — sending report to {$emailOverride}");
        }

        $this->info('Daily sales report sent successfully.');
        return 0;
    }
}
