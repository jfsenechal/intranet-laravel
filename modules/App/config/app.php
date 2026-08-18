<?php

declare(strict_types=1);

return [
    'meilisearch' => [
        'master_key' => env('MEILISEARCH_KEY'),
    ],
    'signature' => [
        'logo_base_url' => env('SIGNATURE_LOGO_BASE_URL', 'https://www.marche.be/logo/'),
    ],
    'sms' => [
        'host' => env('SMS_HOST', 'https://ecom.inforius.be/Api/'),
        'user' => env('SMS_USER'),
        'password' => env('SMS_PASSWORD'),
        'sender' => env('SMS_SENDER'),
    ],
];
