<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    /*
    |--------------------------------------------------------------------------
    | GuichetHdv Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This connection is used by the GuichetHdv module for all database operations.
    |
    */

    'connection' => 'maria-guichet',

    'connections' => [
        'maria-guichet' => [
            'driver' => env('DB_GUICHET_DRIVER', 'mariadb'),
            'url' => env('DB_GUICHET_URL'),
            'host' => env('DB_GUICHET_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_GUICHET_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_GUICHET_DATABASE', 'guichet'),
            'username' => env('DB_GUICHET_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_GUICHET_PASSWORD', env('DB_PASSWORD', '')),
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
