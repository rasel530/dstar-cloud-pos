<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('opened_at');
            $table->uuid('closed_by')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'closed_by']);
        });
    }
};
