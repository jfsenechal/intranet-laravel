<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    */
    'mail' => [
        'redirect_to' => env('MAIL_REDIRECT_TO', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Send Throttling
    |--------------------------------------------------------------------------
    | Controls how fast queued newsletter emails are released to the worker.
    | "per_window" emails are sent every "window_minutes" minutes; within a
    | window the emails are spread evenly to avoid bursting the SMTP server.
    */
    'throttle' => [
        'per_window' => (int) env('MAIL_THROTTLE_PER_WINDOW', 50),
        'window_minutes' => (int) env('MAIL_THROTTLE_WINDOW_MINUTES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unsubscribe
    |--------------------------------------------------------------------------
    | When disabled, newsletters carry no unsubscribe link nor List-Unsubscribe
    | headers, and the subscription status is hidden from the interface.
    | Contacts who already unsubscribed still never receive newsletters.
    */
    'unsubscribe' => [
        'enabled' => (bool) env('MAIL_UNSUBSCRIBE_ENABLED', false),
    ],
    'uploads' => [
        'senders_logos' => 'mailing-list/senders/logos',
        'email_attachments' => 'mailing-list/email-attachments',
    ],
];
