<?php

declare(strict_types=1);

return [
    'validator' => [
        'email' => env('PST_VALIDATOR_EMAIL'),
    ],

    'meili' => [
        'index_name' => env('PST_MEILI_INDEX_NAME', 'pst'),
        'key' => env('MEILISEARCH_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'medias' => 'uploads/pst/medias',
        'odds_icons' => 'uploads/pst/odds',
        'followups_icons' => 'uploads/pst/followups',
    ],
];
