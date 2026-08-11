<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncomeExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->value('id');
        if (!$tenantId) {
            echo "No tenant found.\n";
            return;
        }

        $exists = DB::table('income_expense_categories')->where('tenant_id', $tenantId)->exists();
        if ($exists) {
            echo "Income/Expense categories already exist.\n";
            return;
        }

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

        foreach ($categories as $c) {
            DB::table('income_expense_categories')->insert([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $tenantId,
                'name' => $c['name'],
                'type' => $c['type'],
                'color' => $c['color'],
                'rank' => $c['rank'],
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo count($categories) . " income/expense categories seeded.\n";
    }
}
