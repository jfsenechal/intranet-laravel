<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Courrier Storage
    |--------------------------------------------------------------------------
    |
    | Configure the storage settings for incoming mail attachments.
    |
    */
    'meilisearch' => [
        'index_name' => 'indicateur',
        'filterable_attributes' => [
            'date_courrier_timestamp',
            'recommande',
            'destinataires',
            'services',
            'numero',
            'expediteur',
        ],
        'sortable_attributes' => [
            'date_courrier_timestamp',
            'expediteur',
        ],
    ],
    'imap' => [
        'ville' => [
            'host' => env('IMAP_INDICATEUR_VILLE_HOST'),
            'port' => 993,
            'encryption' => 'ssl',
            'username' => env('IMAP_INDICATEUR_VILLE_USER'),
            'password' => env('IMAP_INDICATEUR_VILLE_PWD'),
        ],
    ],
    'storage' => [
        'disk' => env('COURRIER_DISK', 'public'),
        'directory' => env('COURRIER_DIRECTORY', 'courrier'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Define which file types are allowed for attachment uploads.
    |
    */

    'allowed_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'image/gif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | Maximum file size in kilobytes.
    |
    */

    'max_file_size' => env('COURRIER_MAX_FILE_SIZE', 10240), // 10MB
];
