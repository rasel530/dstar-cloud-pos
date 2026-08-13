<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->uuid('shift_id')->nullable()->index()->after('branch_id');
            $table->string('shift_name', 100)->nullable()->after('shift_id');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'shift_name']);
        });
    }
};
