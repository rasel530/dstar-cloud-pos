<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentTypeExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTypes = [
            ['name' => 'bKash', 'code' => 'bkash', 'is_quick_payment' => true, 'is_change_allowed' => false, 'ordinal' => 4],
            ['name' => 'Nagad', 'code' => 'nagad', 'is_quick_payment' => true, 'is_change_allowed' => false, 'ordinal' => 5],
            ['name' => 'Rocket', 'code' => 'rocket', 'is_quick_payment' => true, 'is_change_allowed' => false, 'ordinal' => 6],
            ['name' => 'Bank Transfer', 'code' => 'bank', 'is_quick_payment' => true, 'is_change_allowed' => false, 'ordinal' => 7],
            ['name' => 'Customer Due', 'code' => 'due', 'is_quick_payment' => true, 'is_change_allowed' => false, 'ordinal' => 8],
        ];

        foreach ($paymentTypes as $pt) {
            DB::table('payment_types')->updateOrInsert(
                ['code' => $pt['code'], 'tenant_id' => null],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $pt['name'],
                    'is_customer_required' => false,
                    'is_fiscal' => true,
                    'is_slip_required' => false,
                    'is_change_allowed' => $pt['is_change_allowed'],
                    'is_quick_payment' => $pt['is_quick_payment'],
                    'is_enabled' => true,
                    'open_cash_drawer' => $pt['code'] === 'cash',
                    'mark_as_paid' => $pt['code'] !== 'due',
                    'ordinal' => $pt['ordinal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Expanded payment types seeded: bKash, Nagad, Rocket, Bank Transfer, Customer Due.');
    }
}
