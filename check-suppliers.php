<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = \App\Models\Customer::where('is_supplier', true)->get(['id','name','code','is_supplier','is_enabled']);
foreach ($c as $r) {
    echo $r->name . ' | code: ' . $r->code . ' | is_supplier: ' . ($r->is_supplier ? 'yes' : 'no') . ' | enabled: ' . ($r->is_enabled ? 'yes' : 'no') . PHP_EOL;
}
echo 'Total: ' . $c->count() . PHP_EOL;
