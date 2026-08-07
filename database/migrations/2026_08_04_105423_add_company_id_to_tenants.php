<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignUuid('company_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('parent_branch_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->boolean('is_company')->default(false);
            $table->string('country', 100)->nullable();
            $table->string('division', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('active');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['parent_branch_id']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'company_id',
                'parent_branch_id',
                'is_company',
                'country',
                'division',
                'district',
                'city',
                'latitude',
                'longitude',
                'manager_id',
                'status',
            ]);
        });
    }
};
