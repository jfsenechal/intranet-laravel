<?php

declare(strict_types=1);

return [
    'filesystems.disks.cpas-library' => [
        'driver' => 'local',
        'root' => storage_path('app/cpas-library'),
        'visibility' => 'private',
        'throw' => false,
    ],

    /*
     * Recipients of the daily reminder and weekly resume digest emails sent by
     * the cpas-library:reminder and cpas-library:resume commands. Comma
     * separated list of email addresses.
     */
    'reminders' => [
        'recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CPAS_LIBRARY_REMINDER_RECIPIENTS', '')),
        ))),
    ],
];
