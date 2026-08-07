<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('price_list_id');
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 12, 4)->default(0);
            $table->decimal('markup', 12, 4)->default(0);
            $table->timestamps();

            $table->index('price_list_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};
