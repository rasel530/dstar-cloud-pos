<?php

return [
    'default' => [
        'paper_width' => 32,
        'printer_type' => 'escpos',
        'number_of_copies' => 1,
        'cut_paper' => true,
        'open_cash_drawer' => true,
        'code_page' => -1,
        'character_set' => -1,
        'barcode_enabled' => true,
        'logo_full_width' => false,
    ],
    'stations' => [
        'receipt',
        'kitchen',
        'bar',
    ],
    'proxy_url' => env('PRINT_PROXY_URL', 'http://localhost:9999'),
    'proxy_token' => env('PRINT_PROXY_TOKEN', 'dstar-print-proxy-2026'),
];
