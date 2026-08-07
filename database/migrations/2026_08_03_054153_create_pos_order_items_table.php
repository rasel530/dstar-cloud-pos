<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pos_order_id');
            $table->foreignUuid('product_id')->constrained('products');
            $table->integer('round_number')->default(0);
            $table->decimal('quantity', 12, 4);
            $table->decimal('price', 12, 4);
            $table->decimal('discount', 12, 4)->default(0);
            $table->smallInteger('discount_type')->default(0);
            $table->smallInteger('discount_applied_type')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignUuid('voided_by')->nullable()->constrained('users');
            $table->text('comment')->nullable();
            $table->text('bundle')->nullable();
            $table->date('date_created')->default(now());
            $table->timestamps();

            $table->index('pos_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};
