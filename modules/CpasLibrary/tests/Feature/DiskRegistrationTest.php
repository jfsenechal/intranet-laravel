<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('registers the cpas-library filesystem disk with a driver', function (): void {
    expect(config('filesystems.disks.cpas-library'))
        ->toBeArray()
        ->and(config('filesystems.disks.cpas-library.driver'))->toBe('local');

    // Resolving the disk must not throw "does not have a configured driver".
    expect(Storage::disk('cpas-library'))->not->toBeNull();
});
