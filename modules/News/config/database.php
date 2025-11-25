<?php



return [
    /*
    |--------------------------------------------------------------------------
    | News Module Database Connection
    |--------------------------------------------------------------------------
    |
    | This connection is used by the News module for all database operations.
    |
    */

    'connection' => 'maria-news',

    'connections' => [
        'maria-news' => [
            'driver' => 'mariadb',
            'url' => env('DB_NEWS_URL'),
            'host' => env('DB_NEWS_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_NEWS_PORT', env('DB_PORT', '3306')),
            'database' => env('DB_NEWS_DATABASE', 'intranet_news'),
            'username' => env('DB_NEWS_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_NEWS_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
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
