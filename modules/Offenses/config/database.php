<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-offenses',
    'connections' => [
        'maria-offenses' => [
            'driver' => env('DB_OFFENSES_DRIVER', 'mariadb'),
            'host' => env('DB_OFFENSES_HOST', '127.0.0.1'),
            'port' => env('DB_OFFENSES_PORT', '3306'),
            'database' => env('DB_OFFENSES_DATABASE', 'sanction'),
            'username' => env('DB_OFFENSES_USERNAME', 'root'),
            'password' => env('DB_OFFENSES_PASSWORD', ''),
            'unix_socket' => env('DB_OFFENSES_SOCKET', ''),
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
