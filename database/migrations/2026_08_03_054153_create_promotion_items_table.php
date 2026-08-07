<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('promotion_id');
            $table->integer('uid');
            $table->smallInteger('discount_type')->default(0);
            $table->smallInteger('price_type')->default(0);
            $table->decimal('value', 12, 4)->default(0);
            $table->boolean('is_conditional')->default(true);
            $table->decimal('quantity', 12, 4)->default(0);
            $table->smallInteger('condition_type')->default(0);
            $table->decimal('quantity_limit', 12, 4)->default(0);
            $table->timestamps();

            $table->index('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_items');
    }
};
