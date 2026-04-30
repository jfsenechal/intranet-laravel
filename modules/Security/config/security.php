<?php

declare(strict_types=1);

return [
    'ldap' => [
        'lists_dn' => env('LDAP_DEFAULT_LIST'),
        'services_dn' => env('LDAP_DEFAULT_SERVICES'),
    ],
];
