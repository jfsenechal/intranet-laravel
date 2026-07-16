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

    /*
     * Nested under this module's own key rather than a top-level 'imap'/'sieve' key: the
     * module service provider only merges config/email-management.php, and the application
     * already ships a config/imap.php that would win the collision.
     */
    'imap' => [
        'citoyen' => [
            'host' => env('IMAP_CITOYEN_URL'),
            'user' => env('IMAP_CITOYEN_ADMIN'),
            'password' => env('IMAP_CITOYEN_PWD'),
        ],
        'employe' => [
            'host' => env('IMAP_EMPLOYE_URL'),
            'user' => env('IMAP_EMPLOYE_ADMIN'),
            'password' => env('IMAP_EMPLOYE_PWD'),
        ],
    ],

    'sieve' => [
        'host' => env('SIEVE_HOST'),
        'port' => env('SIEVE_PORT', 4190),
        'user' => env('SIEVE_ADMIN'),
        'password' => env('SIEVE_PWD'),
    ],

    /*
     * Applied when an address is first assigned, in megabytes. Mirrors the fixed 1024 the
     * legacy GestEmail ImapController used.
     */
    'default_quota_mb' => env('EMAIL_MANAGEMENT_DEFAULT_QUOTA_MB', 1024),
];
