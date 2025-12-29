<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HRM Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This connection is used by the HRM module for all database operations.
    |
    */

    'connection' => 'maria-hrm',
    'connections' => [
        'maria-hrm' => [
            'driver' => env('DB_HRM_DRIVER', 'mariadb'),
            'url' => env('DB_HRM_URL'),
            'host' => env('DB_HRM_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_HRM_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_HRM_DATABASE', 'grh'),
            'username' => env('DB_HRM_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_HRM_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
];
