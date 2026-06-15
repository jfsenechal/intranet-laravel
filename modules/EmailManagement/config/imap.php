<?php

declare(strict_types=1);

return [
    'citoyen' => [
        'host' => env('IMAP_CITOYEN_URL'),
        'user' => env('IMAP_CITOYEN_ADMIN'),
        'password' => env('IMAP_CITOYEN_PWD'),
    ],
    'employe' => [
        'host' => env('IMAP_EMPLOYE_URL'),
        'user' => env('IMAP_EMPLOYE_ADMIN'),
        'password' => env('IMAP_EMPLOYE_PWD'),
    ],
];
