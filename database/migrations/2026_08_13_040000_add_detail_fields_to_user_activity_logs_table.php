<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->string('event', 50)->nullable()->after('action');
            $table->string('reference', 100)->nullable()->after('event');
            $table->uuid('branch_id')->nullable()->after('tenant_id');
            $table->string('device', 255)->nullable()->after('ip_address');
            $table->jsonb('details')->nullable()->after('device');
        });
    }

    public function down(): void
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->dropColumn(['event', 'reference', 'branch_id', 'device', 'details']);
        });
    }
};
