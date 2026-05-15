<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-rescam',
    'connections' => [
        'maria-rescam' => [
            'driver' => env('DB_RESCAM_DRIVER', 'mariadb'),
            'host' => env('DB_RESCAM_HOST', '127.0.0.1'),
            'port' => env('DB_RESCAM_PORT', '3306'),
            'database' => env('DB_RESCAM_DATABASE', 'rescam'),
            'username' => env('DB_RESCAM_USERNAME', 'root'),
            'password' => env('DB_RESCAM_PASSWORD', ''),
            'unix_socket' => env('DB_RESCAM_SOCKET', ''),
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
