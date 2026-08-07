<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('document_id');
            $table->uuid('payment_type_id');
            $table->uuid('user_id');
            $table->uuid('z_report_id')->nullable();
            $table->decimal('amount', 12, 4)->default(0);
            $table->decimal('rounding_adjustment', 12, 4)->default(0);
            $table->date('date')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('document_id');
            $table->index('payment_type_id');
            $table->index('date');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
