<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 100);
            $table->string('order_number', 255)->nullable();
            $table->date('date');
            $table->timestampTz('stock_date');
            $table->decimal('total', 12, 4);
            $table->decimal('discount', 12, 4)->default(0);
            $table->smallInteger('discount_type')->default(0);
            $table->smallInteger('discount_apply_rule')->default(0);
            $table->boolean('is_clocked_out')->default(false);
            $table->string('reference_document_number', 100)->nullable();
            $table->text('internal_note')->nullable();
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();
            $table->smallInteger('paid_status')->default(0);
            $table->smallInteger('service_type')->default(0);
            $table->timestamps();

            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('document_type_id')->constrained('document_types');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');

            $table->index('tenant_id');
            $table->index('number');
            $table->index('date');
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('document_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
