<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Link unlinked branches to their company so tenant/branch isolation
        // can be enforced. Only when exactly one company exists (safe guess).
        $companies = DB::table('tenants')->where('is_company', true)->pluck('id');

        if ($companies->count() === 1) {
            $companyId = $companies->first();
            DB::table('tenants')
                ->where('is_company', false)
                ->whereNull('company_id')
                ->whereNull('parent_branch_id')
                ->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: the linkage is required for isolation.
    }
};
