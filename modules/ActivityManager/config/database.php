<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-activity-manager',
    'connections' => [
        'maria-activity-manager' => [
            'driver' => env('DB_ACTIVITY_MANAGER_DRIVER', 'mariadb'),
            'host' => env('DB_ACTIVITY_MANAGER_HOST', '127.0.0.1'),
            'port' => env('DB_ACTIVITY_MANAGER_PORT', '3306'),
            'database' => env('DB_ACTIVITY_MANAGER_DATABASE', 'mda'),
            'username' => env('DB_ACTIVITY_MANAGER_USERNAME', 'root'),
            'password' => env('DB_ACTIVITY_MANAGER_PASSWORD', ''),
            'unix_socket' => env('DB_ACTIVITY_MANAGER_SOCKET', ''),
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
