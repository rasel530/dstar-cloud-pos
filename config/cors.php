<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Explicit origin allow-list. The application's own URL is always allowed;
    | additional origins can be added via CORS_ALLOWED_ORIGINS (comma-separated)
    | in the environment file.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_merge(
        [rtrim(env('APP_URL', 'http://localhost:8000'), '/')],
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
