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
        Schema::create('pos_printer_selections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 255)->unique();
            $table->string('printer_name', 255)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();

            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_printer_selections');
    }
};
