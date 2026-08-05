<?php

declare(strict_types=1);

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
            'id',
            'reference_number',
            'category_id',
            'mail_date_timestamp',
            'recipients',
            'services',
            'department',
            'is_notified',
            'is_registered',
            'has_acknowledgment',
        ],
        'sortable_attributes' => [
            'mail_date_timestamp',
            'sender',
        ],
    ],
    'imap' => [
        'bgm' => [
            'host' => env('IMAP_INDICATEUR_BGM_HOST'),
            'port' => 993,
            'encryption' => 'ssl',
            'email' => env('IMAP_INDICATEUR_BGM_EMAIL'),
            'username' => env('IMAP_INDICATEUR_BGM_USER'),
            'password' => env('IMAP_INDICATEUR_BGM_PWD'),
        ],
        'cpas' => [
            'host' => env('IMAP_INDICATEUR_CPAS_HOST'),
            'port' => 993,
            'encryption' => 'ssl',
            'email' => env('IMAP_INDICATEUR_CPAS_EMAIL'),
            'username' => env('IMAP_INDICATEUR_CPAS_USER'),
            'password' => env('IMAP_INDICATEUR_CPAS_PWD'),
        ],
        'ville' => [
            'host' => env('IMAP_INDICATEUR_VILLE_HOST'),
            'port' => 993,
            'encryption' => 'ssl',
            'email' => env('IMAP_INDICATEUR_VILLE_EMAIL'),
            'username' => env('IMAP_INDICATEUR_VILLE_USER'),
            'password' => env('IMAP_INDICATEUR_VILLE_PWD'),
        ],
    ],
    'storage' => [
        'disk' => env('COURRIER_DISK', 'local'),
        'directory' => env('COURRIER_DIRECTORY', 'courrier'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Mail
    |--------------------------------------------------------------------------
    |
    | `message_size_limit` mirrors the limit of the same name on the SMTP
    | server: past it the whole message is rejected with a 552 5.3.4 and the
    | recipient never gets their daily digest. Attachments travel base64
    | encoded, which inflates them by about a third, so the raw total is
    | weighted by `encoding_overhead` before the comparison. When the estimate
    | exceeds the limit the notification is sent without any file and carries
    | a notice instead.
    |
    */

    'mail' => [
        'message_size_limit' => env('COURRIER_MAIL_MESSAGE_SIZE_LIMIT', 35000000), // 35MB
        'encoding_overhead' => 1.37,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment OCR
    |--------------------------------------------------------------------------
    |
    | Text extraction settings for the Meilisearch indexer. PDFs with a text
    | layer are read with `pdftotext`; scanned PDFs and images fall back to
    | Tesseract OCR. Requires the poppler-utils and tesseract-ocr binaries
    | (with the configured language pack). Indexing degrades gracefully when
    | a binary is missing.
    |
    */
    'ocr' => [
        'enabled' => env('COURRIER_OCR_ENABLED', true),
        'language' => env('COURRIER_OCR_LANGUAGE', 'fra'),
        'max_pages' => env('COURRIER_OCR_MAX_PAGES', 15),
        'dpi' => env('COURRIER_OCR_DPI', 200),
        'timeout' => env('COURRIER_OCR_TIMEOUT', 120),
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
