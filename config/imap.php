<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailboxes
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the IMAP mailboxes your application connects to.
    | Each mailbox contains its connection settings such as host, port, and
    | credentials. You are free to add as many mailboxes as needed.
    |
    */

    'mailboxes' => [
        'default' => [
            'port' => env('IMAP_PORT', 993),
            'host' => env('IMAP_HOST'),
            'timeout' => env('IMAP_TIMEOUT', 30),
            'debug' => env('IMAP_DEBUG', false),
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'encryption' => env('IMAP_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'authentication' => env('IMAP_AUTHENTICATION', 'plain'),
            'proxy' => [
                'socket' => env('IMAP_PROXY_SOCKET'),
                'username' => env('IMAP_PROXY_USERNAME'),
                'password' => env('IMAP_PROXY_PASSWORD'),
                'request_fulluri' => env('IMAP_PROXY_REQUEST_FULLURI', false),
            ],
        ],

        'indicateur_ville' => [
            'port' => env('IMAP_INDICATEUR_VILLE_PORT', 993),
            'host' => env('IMAP_INDICATEUR_VILLE_HOST'),
            'timeout' => env('IMAP_INDICATEUR_VILLE_TIMEOUT', 30),
            'debug' => env('IMAP_INDICATEUR_VILLE_DEBUG', false),
            'username' => env('IMAP_INDICATEUR_VILLE_USER'),
            'password' => env('IMAP_INDICATEUR_VILLE_PWD'),
            'encryption' => env('IMAP_INDICATEUR_VILLE_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_INDICATEUR_VILLE_VALIDATE_CERT', true),
            'authentication' => env('IMAP_INDICATEUR_VILLE_AUTHENTICATION', 'plain'),
            'proxy' => [
                'socket' => env('IMAP_INDICATEUR_VILLE_PROXY_SOCKET'),
                'username' => env('IMAP_INDICATEUR_VILLE_PROXY_USERNAME'),
                'password' => env('IMAP_INDICATEUR_VILLE_PROXY_PASSWORD'),
                'request_fulluri' => env('IMAP_INDICATEUR_VILLE_PROXY_REQUEST_FULLURI', false),
            ],
        ],
    ],
];
