<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->decimal('rate', 8, 4);
            $table->string('code', 50)->nullable();
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_tax_on_total')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
