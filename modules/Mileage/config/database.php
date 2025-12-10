<?php

return [
    'connections' => [
        'maria-mileage' => [
            'driver' => env('DB_MILEAGE_DRIVER', 'mariadb'),
            'host' => env('DB_MILEAGE_HOST', '127.0.0.1'),
            'port' => env('DB_MILEAGE_PORT', '3306'),
            'database' => env('DB_MILEAGE_DATABASE', 'finance'),
            'username' => env('DB_MILEAGE_USERNAME', 'root'),
            'password' => env('DB_MILEAGE_PASSWORD', ''),
            'unix_socket' => env('DB_MILEAGE_SOCKET', ''),
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
