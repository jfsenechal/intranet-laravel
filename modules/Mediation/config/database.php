<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connection' => 'maria-mediation',
    'connections' => [
        'maria-mediation' => [
            'driver' => env('DB_MEDIATION_DRIVER', 'mariadb'),
            'host' => env('DB_MEDIATION_HOST', '127.0.0.1'),
            'port' => env('DB_MEDIATION_PORT', '3306'),
            'database' => env('DB_MEDIATION_DATABASE', 'mediation'),
            'username' => env('DB_MEDIATION_USERNAME', 'root'),
            'password' => env('DB_MEDIATION_PASSWORD', ''),
            'unix_socket' => env('DB_MEDIATION_SOCKET', ''),
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
