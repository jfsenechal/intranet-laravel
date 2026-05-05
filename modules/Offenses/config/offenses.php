<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Offenses Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Offenses (administrative sanctions) module.
    |
    */
    'storage' => [
        'disk' => env('DOCUMENT_DISK', 'public'),
        'directory' => env('DOCUMENT_DIRECTORY', 'offenses'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'offenses' => 'uploads/offense',
    ],
];
