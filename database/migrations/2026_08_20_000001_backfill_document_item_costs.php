<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // document_items.product_cost was never populated by checkout, so the
        // Profit & Loss report showed COGS = 0. Backfill it from the sale
        // order item costs (documents.order_number -> pos_orders.number ->
        // pos_order_items.product_id/cost).
        $rows = DB::table('document_items as di')
            ->join('documents as d', 'd.id', '=', 'di.document_id')
            ->join('pos_orders as o', 'o.number', '=', 'd.order_number')
            ->join('pos_order_items as oi', function ($join) {
                $join->on('oi.pos_order_id', '=', 'o.id')
                     ->on('oi.product_id', '=', 'di.product_id');
            })
            ->where(function ($q) {
                $q->whereNull('di.product_cost')->orWhere('di.product_cost', 0);
            })
            ->select('di.id', 'oi.cost')
            ->get();

        foreach ($rows as $row) {
            DB::table('document_items')
                ->where('id', $row->id)
                ->update(['product_cost' => (float) $row->cost]);
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
