<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('stock', 12, 4)->default(0);
            $table->decimal('reserved_stock', 12, 4)->default(0);
            $table->decimal('minimum_stock', 12, 4)->default(0);
            $table->decimal('maximum_stock', 12, 4)->default(0);
            $table->decimal('last_purchase_price', 12, 4)->default(0);
            $table->decimal('selling_price', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
            $table->index('tenant_id');
            $table->index('product_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventories');
    }
};
