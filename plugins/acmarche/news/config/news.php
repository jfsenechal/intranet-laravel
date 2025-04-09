<?php
return [
    'connections' => [
        'mariadb' => [
            'driver' => 'mariadb',
            'database' => env('DB_DATABASE_NEWS', 'laravel'),
        ],
    ],
];
