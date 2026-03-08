<?php

declare(strict_types=1);

return [
    'meilisearch' => [
        'master_key' => env('MEILI_MASTER_KEY', null),
    ],
    'webmaster_email' => env('WEBMASTER_EMAIL'),
];
