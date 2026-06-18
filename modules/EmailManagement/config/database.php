<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-email-management',
    'connections' => [
        'maria-email-management' => [
            'driver' => env('DB_EMAIL_MANAGEMENT_DRIVER', 'mariadb'),
            'host' => env('DB_EMAIL_MANAGEMENT_HOST', '127.0.0.1'),
            'port' => env('DB_EMAIL_MANAGEMENT_PORT', '3306'),
            'database' => env('DB_EMAIL_MANAGEMENT_DATABASE', 'telephone'),
            'username' => env('DB_EMAIL_MANAGEMENT_USERNAME', 'root'),
            'password' => env('DB_EMAIL_MANAGEMENT_PASSWORD', ''),
            'unix_socket' => env('DB_EMAIL_MANAGEMENT_SOCKET', ''),
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
