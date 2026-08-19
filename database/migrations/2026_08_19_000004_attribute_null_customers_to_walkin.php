<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sales made without a selected customer were stored with customer_id =
        // NULL, which reports labelled with a hardcoded "Walk-in Customer" group.
        // Attribute them to the tenant's real "Walk-in customer" record so the
        // real customer name is used everywhere (statements, reports, dues).
        $tenants = DB::table('customers')
            ->where('name', 'ilike', '%walk-in%')
            ->select('tenant_id')
            ->distinct()
            ->get();

        foreach ($tenants as $t) {
            if (! $t->tenant_id) continue;

            $walkInId = DB::table('customers')
                ->where('tenant_id', $t->tenant_id)
                ->where('name', 'ilike', '%walk-in%')
                ->orderBy('created_at')
                ->value('id');

            if (! $walkInId) continue;

            DB::table('documents')
                ->where('tenant_id', $t->tenant_id)
                ->whereNull('customer_id')
                ->update(['customer_id' => $walkInId]);

            DB::table('pos_orders')
                ->where('tenant_id', $t->tenant_id)
                ->whereNull('customer_id')
                ->update(['customer_id' => $walkInId]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: attributing walk-in sales to the walk-in customer
        // record is the intended permanent behaviour.
    }
};
