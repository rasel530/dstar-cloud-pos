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
        Schema::create('floor_plan_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('floor_plan_id');
            $table->string('name', 255);
            $table->float('position_x')->default(0);
            $table->float('position_y')->default(0);
            $table->float('width');
            $table->float('height');
            $table->boolean('is_round')->default(false);
            $table->timestamps();

            $table->index('floor_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floor_plan_tables');
    }
};
