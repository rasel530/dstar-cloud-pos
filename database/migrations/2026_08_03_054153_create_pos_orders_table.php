<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('number', 50);
            $table->decimal('discount', 12, 4)->default(0);
            $table->smallInteger('discount_type')->default(0);
            $table->decimal('total', 12, 4)->nullable();
            $table->smallInteger('service_type')->default(0);
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
