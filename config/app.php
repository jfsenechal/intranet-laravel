<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default User Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default user credentials that will be
    | used in local development environments. It is particularly useful
    | for quickly logging into the application without needing
    | to create a user manually.
    |
    */

    'default_user' => [
        'name' => env('DEFAULT_USER_NAME', 'Admin'),
        'email' => env('DEFAULT_USER_EMAIL', 'admin@example.com'),
        'password' => env('DEFAULT_USER_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Timezone
    |--------------------------------------------------------------------------
    |
    | Timestamps are stored in UTC (see the "timezone" option). This value is
    | the timezone they are rendered in for end users: Filament tables, forms
    | and infolists, plus mail and PDF views. Never change "timezone" itself,
    | as that would re-interpret every timestamp already stored in the
    | database and shift all historical dates.
    |
    */

    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Europe/Brussels'),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch
    |--------------------------------------------------------------------------
    |
    | Credentials for the Meilisearch server used by the modules' full-text
    | search (e.g. the Courrier indexer and advanced search).
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'master_key' => env('MEILISEARCH_KEY'),
    ],
];
