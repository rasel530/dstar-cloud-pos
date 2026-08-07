<?php

namespace App\Console\Commands;

use App\Services\Import\AroniumImporter;
use Illuminate\Console\Command;

class ImportAroniumData extends Command
{
    protected $signature = 'aronium:import {path : Path to Aronium pos.db file} {tenant_id : Target tenant UUID}';
    protected $description = 'Import data from Aronium Lite SQLite database';

    public function handle(AroniumImporter $importer): int
    {
        $path = $this->argument('path');
        $tenantId = $this->argument('tenant_id');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Importing data from: {$path}");

        try {
            $summary = $importer->import($path, $tenantId);

            $this->info('Import completed successfully!');
            $this->table(['Table', 'Rows Imported'], collect($summary)->map(fn ($v, $k) => [$k, $v])->toArray());

            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
