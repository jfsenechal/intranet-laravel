<?php

return [
    'connections' => [
        'maria-mileage' => [
            'driver' => 'mariadb',
            'host' => env('MILEAGE_DB_HOST', '127.0.0.1'),
            'port' => env('MILEAGE_DB_PORT', '3306'),
            'database' => env('MILEAGE_DB_DATABASE', 'finance'),
            'username' => env('MILEAGE_DB_USERNAME', 'root'),
            'password' => env('MILEAGE_DB_PASSWORD', ''),
            'unix_socket' => env('MILEAGE_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
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
