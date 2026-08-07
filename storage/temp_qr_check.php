<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$receiptKeys = ['receipt_qr_enabled', 'receipt_qr_base_url', 'receipt_header', 'receipt_footer', 'receipt_copies', 'paper_width', 'receipt_auto_print'];
$settings = Illuminate\Support\Facades\DB::table('application_settings')
    ->whereIn('key', $receiptKeys)
    ->get();

echo "Receipt settings in DB:\n";
foreach ($settings as $s) {
    $val = $s->value;
    if (is_string($val)) $val = trim($val, '"\'');
    echo "  {$s->key}: {$val}\n";
}
