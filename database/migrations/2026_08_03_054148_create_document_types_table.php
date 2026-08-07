<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('code', 50);
            $table->foreignUuid('document_category_id')->constrained('document_categories')->onDelete('cascade');
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->smallInteger('stock_direction')->default(0);
            $table->smallInteger('editor_type')->default(0);
            $table->text('print_template')->nullable();
            $table->smallInteger('price_type')->default(0);
            $table->string('language_key', 100)->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('document_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
