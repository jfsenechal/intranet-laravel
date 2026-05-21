<?php

declare(strict_types=1);

return [
    'uploads' => [
        'agendas_directory' => 'conseil/agendas',
        'notifications_directory' => 'conseil/notifications',
    ],

    'remote' => [
        'base_url' => env('CONSEIL_REMOTE_URL', 'https://www.marche.be/api/'),
        'timeout' => (int) env('CONSEIL_REMOTE_TIMEOUT', 30),
    ],
];
