<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Repository\ServiceRepository;

it('finds a service by its name', function (): void {
    $service = Service::factory()->create(['name' => 'Secrétariat général', 'initials' => 'SG']);
    Service::factory()->create(['name' => 'Travaux', 'initials' => 'TRV']);

    expect(ServiceRepository::searchByNameOrInitials('secrétariat')->all())
        ->toBe([$service->id => 'Secrétariat général']);
});

it('finds a service by its initials', function (): void {
    $service = Service::factory()->create(['name' => 'Secrétariat général', 'initials' => 'SG']);
    Service::factory()->create(['name' => 'Travaux', 'initials' => 'TRV']);

    expect(ServiceRepository::searchByNameOrInitials('SG')->all())
        ->toBe([$service->id => 'Secrétariat général']);
});

it('returns matches on name or initials sorted by name', function (): void {
    $urbanisme = Service::factory()->create(['name' => 'Urbanisme', 'initials' => 'URB']);
    $enseignement = Service::factory()->create(['name' => 'Enseignement', 'initials' => 'URB2']);
    Service::factory()->create(['name' => 'Travaux', 'initials' => 'TRV']);

    expect(ServiceRepository::searchByNameOrInitials('urb')->all())
        ->toBe([
            $enseignement->id => 'Enseignement',
            $urbanisme->id => 'Urbanisme',
        ]);
});
