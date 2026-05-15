<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-conseil',
    'connections' => [
        'maria-conseil' => [
            'driver' => env('DB_CONSEIL_DRIVER', 'mariadb'),
            'host' => env('DB_CONSEIL_HOST', '127.0.0.1'),
            'port' => env('DB_CONSEIL_PORT', '3306'),
            'database' => env('DB_CONSEIL_DATABASE', 'conseil'),
            'username' => env('DB_CONSEIL_USERNAME', 'root'),
            'password' => env('DB_CONSEIL_PASSWORD', ''),
            'unix_socket' => env('DB_CONSEIL_SOCKET', ''),
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
