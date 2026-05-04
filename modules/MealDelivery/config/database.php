<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-meal-delivery',
    'connections' => [
        'maria-meal-delivery' => [
            'driver' => env('DB_MEAL_DELIVERY_DRIVER', 'mariadb'),
            'url' => env('DB_MEAL_DELIVERY_URL'),
            'host' => env('DB_MEAL_DELIVERY_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_MEAL_DELIVERY_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_MEAL_DELIVERY_DATABASE', 'cpas_repas'),
            'username' => env('DB_MEAL_DELIVERY_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_MEAL_DELIVERY_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
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
