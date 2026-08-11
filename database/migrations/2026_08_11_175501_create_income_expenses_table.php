<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('category_id')->constrained('income_expense_categories');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('reference_number', 50)->unique();
            $table->string('type', 10);
            $table->decimal('amount', 14, 4);
            $table->text('description')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->date('date');
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'type']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_expenses');
    }
};
