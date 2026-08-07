<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantId = '85abd15f-3dbe-4fdb-9f93-a322b362c478';
$warehouseId = 'e33270c6-573b-406b-973b-2af28d3f1ff5';

$stocks = Illuminate\Support\Facades\DB::table('stocks')
    ->where('stocks.tenant_id', $tenantId)
    ->where('stocks.warehouse_id', $warehouseId)
    ->join('products', 'stocks.product_id', '=', 'products.id')
    ->select('products.name', 'products.track_inventory', 'products.is_enabled', 'stocks.quantity')
    ->get();

foreach ($stocks as $s) {
    echo sprintf("%-20s track=%s enabled=%s qty=%s\n", $s->name, $s->track_inventory ? 'Y' : 'N', $s->is_enabled ? 'Y' : 'N', $s->quantity);
}
