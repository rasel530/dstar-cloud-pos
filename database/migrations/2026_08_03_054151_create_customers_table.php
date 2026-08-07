<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('code', 100)->nullable();
            $table->string('name', 255);
            $table->string('tax_number', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('street_name', 255)->nullable();
            $table->string('additional_street_name', 255)->nullable();
            $table->string('building_number', 50)->nullable();
            $table->string('plot_identification', 100)->nullable();
            $table->string('city_subdivision_name', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->foreignUuid('country_id')->nullable()->constrained('countries');
            $table->string('country_subentity', 100)->nullable();
            $table->boolean('is_tax_exempt')->default(false);
            $table->boolean('is_supplier')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->integer('due_date_period')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('code');
            $table->index('phone_number');
        });

        DB::statement('CREATE INDEX idx_customers_name ON customers USING GIN (to_tsvector(\'english\', name))');
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
