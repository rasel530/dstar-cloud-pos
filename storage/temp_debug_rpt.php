<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate bestSellingItems
$request = \Illuminate\Http\Request::create('/api/reports/best-selling', 'GET', ['page' => 1, 'per_page' => 25]);
$request->setUserResolver(function () {
    return \App\Models\User::where('email', 'admin@dstar.com')->first();
});

$controller = new \App\Http\Controllers\Api\ReportController(new \App\Services\Reporting\ReportExportService);
$response = $controller->bestSellingItems($request);
$data = json_decode($response->getContent(), true);
echo "=== Best Selling ===\n";
echo "Records: " . count($data['data']['records'] ?? []) . "\n";
echo "Pagination: " . json_encode($data['data']['pagination'] ?? []) . "\n";
echo "Status: " . $response->getStatusCode() . "\n";

// Simulate salesSummary
$request2 = \Illuminate\Http\Request::create('/api/reports/sales-summary', 'GET', ['page' => 1, 'per_page' => 25]);
$request2->setUserResolver(function () {
    return \App\Models\User::where('email', 'admin@dstar.com')->first();
});
$response2 = $controller->salesSummary($request2);
$data2 = json_decode($response2->getContent(), true);
echo "\n=== Sales Summary ===\n";
echo "Records: " . count($data2['data']['records'] ?? []) . "\n";
echo "Pagination: " . json_encode($data2['data']['pagination'] ?? []) . "\n";
echo "Status: " . $response2->getStatusCode() . "\n";
echo "Total orders: " . ($data2['data']['total_orders'] ?? 0) . "\n";

// Check for duplicates in records
$records = $data2['data']['records'] ?? [];
$ids = array_column($records, 'id');
$dupes = count($ids) - count(array_unique($ids));
echo "Duplicate IDs: $dupes\n";
