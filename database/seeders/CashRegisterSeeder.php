<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Morning Shift', 'start_time' => '08:00', 'end_time' => '16:00', 'ordinal' => 0],
            ['name' => 'Evening Shift', 'start_time' => '16:00', 'end_time' => '00:00', 'ordinal' => 1],
            ['name' => 'Night Shift', 'start_time' => '00:00', 'end_time' => '08:00', 'ordinal' => 2],
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->updateOrInsert(
                ['name' => $shift['name'], 'tenant_id' => null],
                [
                    'id' => Str::uuid()->toString(),
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'ordinal' => $shift['ordinal'],
                    'is_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $cashInReasons = ['Owner deposit', 'Cash float top-up', 'Other income'];
        $cashOutReasons = ['Office expense', 'Electricity bill', 'Salary advance', 'Supplier payment', 'Transport'];

        DB::table('application_settings')->updateOrInsert(
            ['key' => 'cash_in_reasons', 'tenant_id' => null],
            ['id' => Str::uuid()->toString(), 'value' => json_encode($cashInReasons), 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('application_settings')->updateOrInsert(
            ['key' => 'cash_out_reasons', 'tenant_id' => null],
            ['id' => Str::uuid()->toString(), 'value' => json_encode($cashOutReasons), 'created_at' => now(), 'updated_at' => now()]
        );

        $this->command->info('Cash register shifts and reasons seeded.');
    }
}
