<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantId = '85abd15f-3dbe-4fdb-9f93-a322b362c478';

// Find a customer with orders
$cust = Illuminate\Support\Facades\DB::table('pos_orders')
    ->where('tenant_id', $tenantId)
    ->whereNotNull('customer_id')
    ->where('status', 'closed')
    ->select('customer_id')
    ->groupBy('customer_id')
    ->orderByRaw('count(*) desc')
    ->first();

if (!$cust) { echo "No customer orders found\n"; exit; }

$customerId = $cust->customer_id;

// Test the same logic as the controller
$dateFilter = function ($q) { /* no date filter */ };
$getBaseQuery = function () use ($tenantId, $customerId, $dateFilter) {
    return \App\Models\PosOrder::where('tenant_id', $tenantId)
        ->where('customer_id', $customerId)
        ->where('status', 'closed')
        ->where($dateFilter);
};

$totalSpent = (float) $getBaseQuery()->sum('total');
$orderCount = $getBaseQuery()->count();
$paginator = $getBaseQuery()->orderByDesc('created_at')->paginate(10);

echo "Customer: $customerId\n";
echo "Orders: $orderCount\n";
echo "Total: $totalSpent\n";
echo "Page 1 of {$paginator->lastPage()}\n";
echo "Orders on page: {$paginator->count()}\n";
