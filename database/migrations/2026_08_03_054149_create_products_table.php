<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('product_group_id')->nullable()->constrained('product_groups')->onDelete('set null');
            $table->string('name', 255);
            $table->string('code', 100)->nullable();
            $table->integer('plu')->nullable()->unique();
            $table->string('measurement_unit', 50)->nullable();
            $table->decimal('price', 12, 4)->default(0);
            $table->decimal('cost', 12, 4)->default(0);
            $table->decimal('markup', 12, 4)->default(0);
            $table->decimal('last_purchase_price', 12, 4)->default(0);
            $table->boolean('is_tax_inclusive_price')->default(true);
            $table->boolean('is_price_change_allowed')->default(false);
            $table->boolean('is_service')->default(false);
            $table->boolean('is_using_default_quantity')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->text('description')->nullable();
            $table->binary('image')->nullable();
            $table->string('color', 50)->default('Transparent');
            $table->integer('age_restriction')->nullable();
            $table->integer('rank')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('product_group_id');
            $table->index('code');
            $table->index('plu');
            $table->index(['tenant_id', 'is_enabled']);
        });

        DB::statement('CREATE INDEX idx_products_name ON products USING GIN (to_tsvector(\'english\', name))');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
