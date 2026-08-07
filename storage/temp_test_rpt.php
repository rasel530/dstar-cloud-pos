<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantId = '85abd15f-3dbe-4fdb-9f93-a322b362c478';

// Test bestSelling
$query = Illuminate\Support\Facades\DB::table('pos_order_items')
    ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
    ->join('products', 'products.id', '=', 'pos_order_items.product_id')
    ->where('pos_orders.tenant_id', $tenantId)
    ->select(
        'products.name',
        Illuminate\Support\Facades\DB::raw('SUM(pos_order_items.quantity) as quantity'),
        Illuminate\Support\Facades\DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) as revenue'),
        Illuminate\Support\Facades\DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) - SUM(COALESCE(products.cost, 0) * pos_order_items.quantity) as profit')
    )
    ->groupBy('pos_order_items.product_id', 'products.name')
    ->orderByDesc('revenue');

$items = $query->get();
echo "Best selling items: " . $items->count() . "\n";
foreach ($items->take(3) as $i) {
    echo "  {$i->name}: qty={$i->quantity} rev={$i->revenue}\n";
}

// Test sales pagination
$paginator = \App\Models\PosOrder::where('tenant_id', $tenantId)
    ->orderByDesc('created_at')->paginate(25);
echo "Sales page 1: " . $paginator->count() . " of " . $paginator->total() . " (last page: " . $paginator->lastPage() . ")\n";
