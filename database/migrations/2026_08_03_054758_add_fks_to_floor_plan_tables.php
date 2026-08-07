<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('floor_plan_tables', function (Blueprint $table) {
            $table->foreign('floor_plan_id')->references('id')->on('floor_plans')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::table('floor_plan_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_plan_id']);
        });
    }
};
