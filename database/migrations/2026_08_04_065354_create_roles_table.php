<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->integer('access_level')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['id' => Str::uuid()->toString(), 'name' => 'Cashier', 'access_level' => 0, 'description' => 'Basic POS access', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid()->toString(), 'name' => 'Manager', 'access_level' => 5, 'description' => 'Manage products, reports, customers', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid()->toString(), 'name' => 'Admin', 'access_level' => 9, 'description' => 'Full system access', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('module', 50);
            $table->timestamps();
            $table->unique(['role_id', 'module']);
        });

        $allModules = array_keys(config('modules.list', []));
        if (empty($allModules)) {
            $allModules = ['pos','orders','products','customers','reports','users','settings','branches','promotions','loyalty','taxes','printers','fiscal','dashboard'];
        }

        $adminOnly = ['users','settings','branches','taxes','fiscal','printers','roles'];

        $cashierId = DB::table('roles')->where('access_level', 0)->value('id');
        $managerId = DB::table('roles')->where('access_level', 5)->value('id');
        $adminId = DB::table('roles')->where('access_level', 9)->value('id');

        foreach (config('modules.list', []) as $key => $info) {
            if (($info['min_level'] ?? 99) <= 0) {
                DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $cashierId, 'module' => $key, 'created_at' => now(), 'updated_at' => now()]);
            }
            if (($info['min_level'] ?? 99) <= 5) {
                DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $managerId, 'module' => $key, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $adminId, 'module' => $key, 'created_at' => now(), 'updated_at' => now()]);
        }

        if (empty(config('modules.list', []))) {
            foreach (['pos','orders'] as $m) {
                DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $cashierId, 'module' => $m, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($allModules as $m) {
                if (!in_array($m, $adminOnly)) {
                    DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $managerId, 'module' => $m, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            foreach ($allModules as $m) {
                DB::table('role_permissions')->insert(['id' => Str::uuid()->toString(), 'role_id' => $adminId, 'module' => $m, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
