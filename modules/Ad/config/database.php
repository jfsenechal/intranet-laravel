<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    /*
    |--------------------------------------------------------------------------
    | Ad Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This connection is used by the Ad module for all database operations.
    |
    */

    'connection' => 'maria-ad',

    'connections' => [
        'maria-ad' => [
            'driver' => env('DB_AD_DRIVER', 'mariadb'),
            'url' => env('DB_AD_URL'),
            'host' => env('DB_AD_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_AD_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_AD_DATABASE', 'intranet_news'),
            'username' => env('DB_AD_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_AD_PASSWORD', env('DB_PASSWORD', '')),
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
