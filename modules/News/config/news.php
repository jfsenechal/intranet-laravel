<?php

return [
    /*
    |--------------------------------------------------------------------------
    | News Settings
    |--------------------------------------------------------------------------
    |
    | Configure the settings for the news module.
    |
    */

    'pagination' => [
        'per_page' => env('NEWS_PER_PAGE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Featured Image
    |--------------------------------------------------------------------------
    |
    | Configure featured image settings.
    |
    */

    'featured_image' => [
        'disk' => env('NEWS_IMAGE_DISK', 'public'),
        'directory' => env('NEWS_IMAGE_DIRECTORY', 'news'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Predefined news categories.
    |
    */

    'categories' => [
        'General',
        'Announcement',
        'Event',
        'Update',
        'Other',
    ],
];
