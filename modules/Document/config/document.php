<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Storage
    |--------------------------------------------------------------------------
    |
    | Configure the storage settings for documents.
    |
    */

    'storage' => [
        'disk' => env('DOCUMENT_DISK', 'public'),
        'directory' => env('DOCUMENT_DIRECTORY', 'documents'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Define which file types are allowed for upload.
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

    'max_file_size' => env('DOCUMENT_MAX_FILE_SIZE', 10240), // 10MB

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Predefined document categories.
    |
    */

    'categories' => [
        'General',
        'Policy',
        'Procedure',
        'Report',
        'Other',
    ],
];
