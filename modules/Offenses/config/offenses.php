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
        'disk' => env('OFFENSE_DISK', 'local'),
        'directory' => env('OFFENSE_DIRECTORY', 'uploads/offense'),
    ],
];
