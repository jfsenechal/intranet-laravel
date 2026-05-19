<?php

declare(strict_types=1);

return [
    'filesystems.disks.cpas-library' => [
        'driver' => 'local',
        'root' => storage_path('app/cpas-library'),
        'visibility' => 'private',
        'throw' => false,
    ],
];
