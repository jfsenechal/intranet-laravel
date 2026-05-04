<?php

declare(strict_types=1);

return [
    'connections' => [
        'maria-meal-delivery' => [
            'driver' => 'mysql',
            'host' => env('CPASREPAS_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('CPASREPAS_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('CPASREPAS_DB_DATABASE', 'meal-delivery'),
            'username' => env('CPASREPAS_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('CPASREPAS_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
    ],
];
