<?php

declare(strict_types=1);

return [
    'filesystems.disks.knowledge-base' => [
        'driver' => 'local',
        'root' => storage_path('app/private/knowledge-base'),
        'visibility' => 'private',
        'serve' => false,
        'throw' => true,
    ],

    /*
     * Link to the password vault, displayed on every page as a reminder that
     * secrets never belong in the knowledge base.
     */
    'vault_url' => env('KNOWLEDGE_BASE_VAULT_URL'),
];
