<?php

declare(strict_types=1);

return [
    'ldap' => [
        'bases' => [
            'employes' => env('LDAP_DEFAULT_BASE_DN'),
            'lists' => env('LDAP_STAFF_LIST_BASE'),
            'services' => env('LDAP_STAFF_SERVICES_BASE'),
        ],
    ],
];
