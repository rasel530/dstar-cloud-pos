<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->timestamp('held_at')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('held_at');
            $table->timestamp('expired_at')->nullable()->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['held_at', 'closed_at', 'expired_at']);
        });
    }
};
