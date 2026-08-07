<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('expected_quantity', 12, 4)->default(0);
            $table->decimal('price', 12, 4);
            $table->decimal('price_before_tax', 12, 4)->default(0);
            $table->decimal('price_before_tax_after_discount', 12, 4)->default(0);
            $table->decimal('price_after_discount', 12, 4)->default(0);
            $table->decimal('discount', 12, 4)->default(0);
            $table->smallInteger('discount_type')->default(0);
            $table->smallInteger('discount_apply_rule')->default(0);
            $table->decimal('product_cost', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('total_after_document_discount', 12, 4)->default(0);
            $table->timestamps();

            $table->foreignUuid('product_id')->constrained('products');

            $table->index('document_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_items');
    }
};
