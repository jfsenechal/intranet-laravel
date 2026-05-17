<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-cpas-library',
    'connections' => [
        'maria-cpas-library' => [
            'driver' => env('DB_CPAS_LIBRARY_DRIVER', 'mariadb'),
            'host' => env('DB_CPAS_LIBRARY_HOST', '127.0.0.1'),
            'port' => env('DB_CPAS_LIBRARY_PORT', '3306'),
            'database' => env('DB_CPAS_LIBRARY_DATABASE', 'library'),
            'username' => env('DB_CPAS_LIBRARY_USERNAME', 'root'),
            'password' => env('DB_CPAS_LIBRARY_PASSWORD', ''),
            'unix_socket' => env('DB_CPAS_LIBRARY_SOCKET', ''),
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
