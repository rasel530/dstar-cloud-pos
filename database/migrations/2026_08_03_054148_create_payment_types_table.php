<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->boolean('is_customer_required')->default(false);
            $table->boolean('is_fiscal')->default(true);
            $table->boolean('is_slip_required')->default(false);
            $table->boolean('is_change_allowed')->default(true);
            $table->boolean('is_quick_payment')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('open_cash_drawer')->default(true);
            $table->string('shortcut_key', 10)->nullable();
            $table->boolean('mark_as_paid')->default(true);
            $table->decimal('rounding_increment', 10, 4)->default(0);
            $table->smallInteger('rounding_rule')->default(0);
            $table->integer('ordinal')->default(0);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
