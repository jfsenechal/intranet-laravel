<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | HRM Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Human Resource Management module.
    |
    */
    'name' => 'HRM',
    'description' => 'Human Resource Management Module',
    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'documents' => 'hrm/documents',
        'photos' => 'hrm/photos',
        'contracts' => 'hrm/contracts',
        'diplomas' => 'hrm/diplomas',
        'evaluations' => 'hrm/evaluations',
        'formations' => 'hrm/formations',
        'valorizations' => 'hrm/valorizations',
        'candidates' => 'hrm/candidates',
        'students' => 'hrm/students',
    ],
    /*
    |--------------------------------------------------------------------------
    | HR Team Email Addresses
    |--------------------------------------------------------------------------
    |
    | Recipients notified after a telework request has been validated by the
    | manager. Comma separated list read from the HRM_TEAM_EMAILS env variable.
    |
    */
    'team_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('HRM_TEAM_EMAILS', 'grh@domain.be,grh.cpas@domain.be'))
    ))),
    /*
    |--------------------------------------------------------------------------
    | Daily Reminder Recipients
    |--------------------------------------------------------------------------
    |
    | Recipients of the `hrm:reminders {department}` command output, keyed by
    | the employer slug ("ville" or "cpas"). Comma separated env values.
    |
    */
    'reminders' => [
        'recipients' => [
            'ville' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) env('HRM_REMINDERS_VILLE_TO', ''))
            ))),
            'cpas' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) env('HRM_REMINDERS_CPAS_TO', ''))
            ))),
        ],
    ],
];
