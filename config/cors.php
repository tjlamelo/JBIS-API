<?php

$frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

$allowedOrigins = array_values(array_unique(array_filter([
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'https://jbis.cm',
    'https://www.jbis.cm',
    $frontendUrl,
    $frontendUrl !== '' ? preg_replace('#^http://#', 'https://', $frontendUrl) : null,
])));

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
