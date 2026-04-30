<?php

declare(strict_types=1);

return [
    'meilisearch' => [
        'master_key' => env('MEILI_MASTER_KEY'),
    ],
    'webmaster_email' => env('WEBMASTER_EMAIL'),
    'sms' => [
        'host' => env('SMS_HOST', 'https://ecom.inforius.be/Api/'),
        'user' => env('SMS_USER'),
        'password' => env('SMS_PASSWORD'),
        'sender' => env('SMS_SENDER'),
    ],
];
