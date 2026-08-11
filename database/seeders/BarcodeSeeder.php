<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BarcodeSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->limit(10)->pluck('id');
        foreach ($products as $pid) {
            $value = '2' . random_int(100000, 999999);
            DB::table('barcodes')->insert([
                'id' => Str::uuid()->toString(),
                'product_id' => $pid,
                'value' => $value,
                'barcode_type' => 'CODE_128',
                'is_primary' => true,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo count($products) . " barcodes seeded.\n";
    }
}
