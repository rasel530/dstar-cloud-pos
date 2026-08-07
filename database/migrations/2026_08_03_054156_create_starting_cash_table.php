<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('starting_cash', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount', 12, 4);
            $table->text('description')->nullable();
            $table->smallInteger('starting_cash_type')->default(0);
            $table->integer('z_report_number')->nullable();
            $table->timestamps();

            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users');

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starting_cash');
    }
};
