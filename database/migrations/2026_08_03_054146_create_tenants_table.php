<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->string('subdomain', 100)->nullable()->unique();
            $table->string('plan_type', 50)->default('lite');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('subscription_ends_at')->nullable();
            $table->jsonb('settings')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
