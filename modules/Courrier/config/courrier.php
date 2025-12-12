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

    /*
    |--------------------------------------------------------------------------
    | Mail Status Options
    |--------------------------------------------------------------------------
    |
    | Available status options for incoming mail.
    |
    */

    'statuses' => [
        'pending' => 'Pending',
        'processed' => 'Processed',
        'archived' => 'Archived',
    ],
];
