<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Non-person records
    |--------------------------------------------------------------------------
    |
    | The HRM `employees` table doubles as a document folder store: unions,
    | external partners, administrative procedures and reference sheets are
    | encoded as employee records, recognizable by a marker at the start of
    | their name ("A - SYNDICAT CGSP", "@ RH", "PROCEDURE", "DMFA Ville", ...).
    |
    | These entries are never real agents and must stay out of the directory.
    | Values are SQL LIKE patterns, matched on both `last_name` and `first_name`.
    |
    */

    'excluded_name_patterns' => [
        'A -%',
        'A-%',
        '@%',
        'PROCEDURE%',
        'DMFA%',
    ],

];
