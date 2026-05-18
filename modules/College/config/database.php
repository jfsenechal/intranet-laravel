<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-college',
    'connections' => [
        'maria-college' => [
            'driver' => env('DB_COLLEGE_DRIVER', 'mariadb'),
            'host' => env('DB_COLLEGE_HOST', '127.0.0.1'),
            'port' => env('DB_COLLEGE_PORT', '3306'),
            'database' => env('DB_COLLEGE_DATABASE', 'college'),
            'username' => env('DB_COLLEGE_USERNAME', 'root'),
            'password' => env('DB_COLLEGE_PASSWORD', ''),
            'unix_socket' => env('DB_COLLEGE_SOCKET', ''),
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
