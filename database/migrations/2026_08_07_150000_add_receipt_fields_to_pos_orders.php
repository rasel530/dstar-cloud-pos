<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 4)->nullable()->after('total');
            }
            if (!Schema::hasColumn('pos_orders', 'change_amount')) {
                $table->decimal('change_amount', 12, 4)->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('pos_orders', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('change_amount');
            }
            if (!Schema::hasColumn('pos_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 4)->default(0)->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'change_amount', 'payment_method', 'tax_amount']);
        });
    }
};
