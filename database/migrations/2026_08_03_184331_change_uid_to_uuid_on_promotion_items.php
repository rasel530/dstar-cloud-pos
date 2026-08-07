<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_items', function (Blueprint $table) {
            $table->string('uid')->change();
        });
        Schema::table('customer_discounts', function (Blueprint $table) {
            $table->string('uid')->nullable()->change();
        });
    }

    public function down(): void
    {
        // No rollback for string→integer conversion with data
    }
};
