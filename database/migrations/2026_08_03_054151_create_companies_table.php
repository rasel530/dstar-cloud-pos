<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
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
            $table->string('tax_number', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->text('bank_details')->nullable();
            $table->binary('logo')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
