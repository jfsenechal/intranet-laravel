<?php

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
        'documents' => 'uploads/hrm/documents',
        'photos' => 'uploads/hrm/photos',
        'contracts' => 'uploads/hrm/contracts',
        'diplomas' => 'uploads/hrm/diplomas',
        'evaluations' => 'uploads/hrm/evaluations',
        'formations' => 'uploads/hrm/formations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Employee Statuses
    |--------------------------------------------------------------------------
    */
    'employee_statuses' => [
        'active' => 'Actif',
        'retired' => 'Pension',
        'terminated' => 'Sorti',
        'suspended' => 'Suspendu',
    ],
];
