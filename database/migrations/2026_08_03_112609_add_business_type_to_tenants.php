<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('business_type', 50)->default('retail')->after('plan_type');
            $table->string('branch_code', 50)->nullable()->after('slug');
            $table->text('address')->nullable()->after('business_type');
            $table->string('phone', 50)->nullable()->after('address');
            $table->boolean('is_headquarters')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'branch_code', 'address', 'phone', 'is_headquarters']);
        });
    }
};
