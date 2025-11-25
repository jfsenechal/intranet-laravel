<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Publication Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This connection is used by the Publication module for all database operations.
    |
    */

    'connection' => 'maria-publication',

    'connections' => [
        'maria-publication' => [
            'driver' => 'mariadb',
            'url' => env('DB_PUBLICATION_URL'),
            'host' => env('DB_PUBLICATION_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_PUBLICATION_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_PUBLICATION_DATABASE', 'publication'),
            'username' => env('DB_PUBLICATION_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_PUBLICATION_PASSWORD', env('DB_PASSWORD', '')),
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
