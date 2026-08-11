<?php

use Illuminate\Support\Str;

// Find admin user's tenant
$userTenant = DB::table('users')->where('email', 'admin@dstar.com')->value('tenant_id');
echo "User tenant: $userTenant\n";

$existing = DB::table('income_expense_categories')->first();
echo "Cat tenant: " . ($existing->tenant_id ?? 'none') . "\n";

if ($existing && $existing->tenant_id !== $userTenant) {
    // Delete old categories and re-create with correct tenant
    DB::table('income_expense_categories')->truncate();
    echo "Old categories deleted.\n";
    
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
            'tenant_id' => $userTenant,
            'name' => $c['name'],
            'type' => $c['type'],
            'color' => $c['color'],
            'rank' => $c['rank'],
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo count($categories) . " categories re-seeded with correct tenant.\n";
} else {
    echo "Tenant matches, no update needed.\n";
}
