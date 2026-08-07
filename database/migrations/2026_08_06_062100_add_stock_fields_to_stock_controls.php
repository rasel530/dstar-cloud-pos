<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_controls', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_controls', 'minimum_stock')) {
                $table->decimal('minimum_stock', 12, 4)->default(0)->after('low_stock_warning_quantity');
            }
            if (!Schema::hasColumn('stock_controls', 'maximum_stock')) {
                $table->decimal('maximum_stock', 12, 4)->default(0)->after('minimum_stock');
            }
            if (!Schema::hasColumn('stock_controls', 'opening_stock')) {
                $table->decimal('opening_stock', 12, 4)->default(0)->after('maximum_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_controls', function (Blueprint $table) {
            $table->dropColumn(['minimum_stock', 'maximum_stock', 'opening_stock']);
        });
    }
};
