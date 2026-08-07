<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_voids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('order_number', 255);
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 255);
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 255);
            $table->integer('round_number');
            $table->decimal('quantity', 12, 4);
            $table->decimal('price', 12, 4);
            $table->decimal('discount', 12, 4);
            $table->smallInteger('discount_type');
            $table->decimal('total', 12, 4);
            $table->boolean('is_confirmed');
            $table->text('reason')->nullable();
            $table->foreignUuid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('voided_by_name', 255)->nullable();
            $table->text('bundle')->nullable();
            $table->timestampTz('date_created');
            $table->timestampTz('date_voided');
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_voids');
    }
};
