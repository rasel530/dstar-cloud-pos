<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'id' => Str::uuid()->toString(),
            'name' => 'Default Store',
            'slug' => 'default',
            'plan_type' => 'lite',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'username' => 'admin',
            'email' => 'admin@dstar.com',
            'password' => bcrypt('admin123'),
            'access_level' => 9,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'name' => 'Walk-in customer',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('warehouses')->insert([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'name' => 'Warehouse',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $taxId = DB::table('taxes')->insertGetId([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'name' => 'VAT',
            'rate' => 10.00,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productGroups = [
            ['id' => Str::uuid()->toString(), 'tenant_id' => $tenantId, 'name' => 'Coffee', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $tenantId, 'name' => 'Food', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $tenantId, 'name' => 'Beverages', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('product_groups')->insert($productGroups);

        $products = [
            ['name' => 'Espresso', 'code' => 'COF001', 'price' => 3.50, 'cost' => 1.20, 'group' => 0, 'color' => '#3b82f6'],
            ['name' => 'Latte', 'code' => 'COF002', 'price' => 4.50, 'cost' => 1.80, 'group' => 0, 'color' => '#8b5cf6'],
            ['name' => 'Cappuccino', 'code' => 'COF003', 'price' => 4.00, 'cost' => 1.60, 'group' => 0, 'color' => '#06b6d4'],
            ['name' => 'Mocha', 'code' => 'COF004', 'price' => 5.00, 'cost' => 2.00, 'group' => 0, 'color' => '#f59e0b'],
            ['name' => 'Americano', 'code' => 'COF005', 'price' => 3.00, 'cost' => 1.00, 'group' => 0, 'color' => '#10b981'],
            ['name' => 'Hot Chocolate', 'code' => 'BEV001', 'price' => 4.00, 'cost' => 1.50, 'group' => 2, 'color' => '#ef4444'],
            ['name' => 'Green Tea', 'code' => 'BEV002', 'price' => 2.50, 'cost' => 0.80, 'group' => 2, 'color' => '#14b8a6'],
            ['name' => 'Chai Latte', 'code' => 'BEV003', 'price' => 4.50, 'cost' => 1.80, 'group' => 2, 'color' => '#ec4899'],
            ['name' => 'Iced Coffee', 'code' => 'BEV004', 'price' => 4.00, 'cost' => 1.50, 'group' => 2, 'color' => '#6366f1'],
            ['name' => 'Smoothie', 'code' => 'BEV005', 'price' => 6.00, 'cost' => 2.50, 'group' => 2, 'color' => '#f97316'],
            ['name' => 'Croissant', 'code' => 'FOD001', 'price' => 3.50, 'cost' => 1.20, 'group' => 1, 'color' => '#d946ef'],
            ['name' => 'Muffin', 'code' => 'FOD002', 'price' => 3.00, 'cost' => 1.00, 'group' => 1, 'color' => '#0ea5e9'],
            ['name' => 'Bagel', 'code' => 'FOD003', 'price' => 3.50, 'cost' => 1.10, 'group' => 1, 'color' => '#84cc16'],
            ['name' => 'Sandwich', 'code' => 'FOD004', 'price' => 7.00, 'cost' => 3.00, 'group' => 1, 'color' => '#eab308'],
            ['name' => 'Salad', 'code' => 'FOD005', 'price' => 8.00, 'cost' => 3.50, 'group' => 1, 'color' => '#22c55e'],
            ['name' => 'Soup', 'code' => 'FOD006', 'price' => 5.00, 'cost' => 2.00, 'group' => 1, 'color' => '#a855f7'],
            ['name' => 'Cake Slice', 'code' => 'FOD007', 'price' => 5.50, 'cost' => 2.00, 'group' => 1, 'color' => '#f43f5e'],
            ['name' => 'Cookie', 'code' => 'FOD008', 'price' => 2.50, 'cost' => 0.80, 'group' => 1, 'color' => '#14b8a6'],
            ['name' => 'Brownie', 'code' => 'FOD009', 'price' => 4.00, 'cost' => 1.50, 'group' => 1, 'color' => '#e11d48'],
            ['name' => 'Water', 'code' => 'BEV006', 'price' => 1.50, 'cost' => 0.30, 'group' => 2, 'color' => '#3b82f6'],
        ];

        $productRows = [];
        foreach ($products as $p) {
            $productRows[] = [
                'id' => Str::uuid()->toString(),
                'tenant_id' => $tenantId,
                'product_group_id' => $productGroups[$p['group']]['id'],
                'name' => $p['name'],
                'code' => $p['code'],
                'price' => $p['price'],
                'cost' => $p['cost'],
                'color' => $p['color'],
                'is_enabled' => true,
                'is_tax_inclusive_price' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('products')->insert($productRows);

        // Seed default application settings
        $defaultSettings = [
            ['key' => 'company_name', 'value' => 'My Store'],
            ['key' => 'currency', 'value' => 'USD'],
            ['key' => 'timezone', 'value' => 'UTC'],
            ['key' => 'grid_columns', 'value' => '4'],
            ['key' => 'grid_rows', 'value' => '4'],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('application_settings')->updateOrInsert(
                ['tenant_id' => $tenantId, 'key' => $setting['key']],
                ['id' => Str::uuid()->toString(), 'value' => $setting['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        echo "Default tenant, admin user (admin@dstar.com / admin123), walk-in customer & warehouse created.\n";
        echo "Tax (10%), 3 product groups & 20 demo products created.\n";

        // Seed default income/expense categories
        $categories = [
            ['name' => 'Sales Revenue', 'type' => 'income', 'color' => '#10b981', 'rank' => 1],
            ['name' => 'Service Income', 'type' => 'income', 'color' => '#3b82f6', 'rank' => 2],
            ['name' => 'Interest Income', 'type' => 'income', 'color' => '#8b5cf6', 'rank' => 3],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#f59e0b', 'rank' => 4],
            ['name' => 'Rent & Utilities', 'type' => 'expense', 'color' => '#ef4444', 'rank' => 1],
            ['name' => 'Salaries & Wages', 'type' => 'expense', 'color' => '#f97316', 'rank' => 2],
            ['name' => 'Supplies & Materials', 'type' => 'expense', 'color' => '#06b6d4', 'rank' => 3],
            ['name' => 'Marketing', 'type' => 'expense', 'color' => '#ec4899', 'rank' => 4],
            ['name' => 'Maintenance', 'type' => 'expense', 'color' => '#6366f1', 'rank' => 5],
            ['name' => 'Travel & Transport', 'type' => 'expense', 'color' => '#14b8a6', 'rank' => 6],
            ['name' => 'Taxes & Licenses', 'type' => 'expense', 'color' => '#78716c', 'rank' => 7],
            ['name' => 'Other Expenses', 'type' => 'expense', 'color' => '#6b7280', 'rank' => 8],
        ];

        foreach ($categories as $cat) {
            DB::table('income_expense_categories')->insert([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $tenantId,
                'name' => $cat['name'],
                'type' => $cat['type'],
                'color' => $cat['color'],
                'rank' => $cat['rank'],
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "12 Income & Expense categories seeded.\n";
    }
}
