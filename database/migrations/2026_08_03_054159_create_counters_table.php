<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 255);
            $table->integer('value');
            $table->timestamps();

            $table->primary(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};
