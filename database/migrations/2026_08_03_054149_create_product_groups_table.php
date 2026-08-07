<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_groups', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 255);
            $table->uuid('parent_group_id')->nullable();
            $table->string('color', 50)->default('Transparent');
            $table->binary('image')->nullable();
            $table->integer('rank')->default(0);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::table('product_groups', function (Blueprint $table) {
            $table->foreign('parent_group_id')->references('id')->on('product_groups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_groups');
    }
};
