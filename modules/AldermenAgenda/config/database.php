<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-aldermen-agenda',

    'connections' => [
        'maria-aldermen-agenda' => [
            'driver' => env('DB_ALDERMEN_AGENDA_DRIVER', 'mariadb'),
            'url' => env('DB_ALDERMEN_AGENDA_URL'),
            'host' => env('DB_ALDERMEN_AGENDA_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_ALDERMEN_AGENDA_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_ALDERMEN_AGENDA_DATABASE', 'intranet_agenda_echevin'),
            'username' => env('DB_ALDERMEN_AGENDA_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_ALDERMEN_AGENDA_PASSWORD', env('DB_PASSWORD', '')),
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
