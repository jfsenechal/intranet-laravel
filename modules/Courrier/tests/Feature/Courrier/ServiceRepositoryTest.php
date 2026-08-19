<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
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

it('resolves the codes stamped on a mail, whatever their case', function (): void {
    $humanResources = Service::factory()->create(['name' => 'Ressources Humaines', 'initials' => 'RH']);
    $childhood = Service::factory()->create(['name' => 'Coordination Education Enfance', 'initials' => 'CEE']);
    Service::factory()->create(['name' => 'Travaux', 'initials' => 'TRV']);

    expect(ServiceRepository::findIdsByCodes([' rh ', 'Cee', 'INCONNU', '']))
        ->toBe([$humanResources->id, $childhood->id]);
});

it('resolves a code written out in full', function (): void {
    $service = Service::factory()->create(['name' => 'Enseignement', 'initials' => 'ENS']);

    expect(ServiceRepository::findIdsByCodes(['Enseignement']))->toBe([$service->id]);
});

it('drops a code shared by several services rather than guessing', function (): void {
    Service::factory()->create(['name' => 'Musée', 'initials' => 'MUS']);
    Service::factory()->create(['name' => 'Conservatoire de Musique', 'initials' => 'MUS']);

    expect(ServiceRepository::findIdsByCodes(['MUS']))->toBe([]);
});

it('resolves a code inside the given department only', function (): void {
    $ville = Service::factory()->create([
        'name' => 'Ressources Humaines',
        'initials' => 'RH',
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);
    Service::factory()->create([
        'name' => 'Ressources humaines',
        'initials' => 'RH',
        'department' => DepartmentCourrierEnum::BGM->value,
    ]);

    expect(ServiceRepository::findIdsByCodes(['RH'], DepartmentCourrierEnum::VILLE))->toBe([$ville->id])
        // Without a department the two are indistinguishable, so neither is used.
        ->and(ServiceRepository::findIdsByCodes(['RH']))->toBe([]);
});
