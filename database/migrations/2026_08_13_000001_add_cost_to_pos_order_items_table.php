<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->decimal('cost', 12, 4)->default(0)->after('price');
        });

        DB::statement('
            UPDATE pos_order_items oi
            SET cost = COALESCE(p.cost, 0)
            FROM products p
            WHERE oi.product_id = p.id
        ');
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
