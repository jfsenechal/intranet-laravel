<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-street-watch',
    'connections' => [
        'maria-street-watch' => [
            'driver' => env('DB_STREET_WATCH_DRIVER', 'mariadb'),
            'host' => env('DB_STREET_WATCH_HOST', '127.0.0.1'),
            'port' => env('DB_STREET_WATCH_PORT', '3306'),
            'database' => env('DB_STREET_WATCH_DATABASE', 'street'),
            'username' => env('DB_STREET_WATCH_USERNAME', 'root'),
            'password' => env('DB_STREET_WATCH_PASSWORD', ''),
            'unix_socket' => env('DB_STREET_WATCH_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
];
