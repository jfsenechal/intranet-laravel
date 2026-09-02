<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use AcMarche\WhoIsWho\Filament\Exports\EmployeeDirectoryExport;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;

/**
 * @param  array<string, mixed>  $contractAttributes
 */
function directoryContract(Employee $employee, array $contractAttributes = []): Contract
{
    return Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'start_date' => now()->subMonth(),
        'end_date' => null,
        ...$contractAttributes,
    ]);
}

function directoryAgent(string $firstName, string $lastName): Employee
{
    return Employee::factory()->create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
    ]);
}

it('exports the name, service, direction and function of an agent', function (): void {
    $direction = Direction::factory()->create(['name' => 'Direction generale']);
    $service = Service::factory()->create(['name' => 'Informatique', 'direction_id' => $direction->id]);

    $employee = directoryAgent('Marie', 'Dupont');
    directoryContract($employee, [
        'service_id' => $service->id,
        'direction_id' => $direction->id,
        'job_title' => 'Analyste',
    ]);

    $export = new EmployeeDirectoryExport(EmployeeRepository::activeAgentsQuery());

    expect($export->contractSlots())->toBe(1)
        ->and($export->headings(1))->toBe(['Prenom', 'Nom', 'Service', 'Direction', 'Fonction'])
        ->and($export->map($employee->fresh(['activeContracts.service', 'activeContracts.direction']), 1))
        ->toBe(['Marie', 'Dupont', 'Informatique', 'Direction generale', 'Analyste']);
});

it('repeats the contract columns when an agent holds several contracts', function (): void {
    $employee = directoryAgent('Jean', 'Martin');

    $first = Service::factory()->create(['name' => 'Urbanisme']);
    $second = Service::factory()->create(['name' => 'Travaux']);

    directoryContract($employee, ['service_id' => $first->id, 'job_title' => 'Agent']);
    directoryContract($employee, ['service_id' => $second->id, 'job_title' => 'Coordinateur']);

    $export = new EmployeeDirectoryExport(EmployeeRepository::activeAgentsQuery());

    expect($export->contractSlots())->toBe(2)
        ->and($export->headings(2))->toBe([
            'Prenom', 'Nom',
            'Service', 'Direction', 'Fonction',
            'Service 2', 'Direction 2', 'Fonction 2',
        ]);
});

it('leaves the extra contract columns empty for an agent with a single contract', function (): void {
    $service = Service::factory()->create(['name' => 'Urbanisme']);

    $single = directoryAgent('Anne', 'Lambert');
    directoryContract($single, ['service_id' => $service->id, 'job_title' => 'Agent', 'direction_id' => null]);

    $double = directoryAgent('Jean', 'Martin');
    directoryContract($double);
    directoryContract($double);

    $export = new EmployeeDirectoryExport(EmployeeRepository::activeAgentsQuery());

    expect($export->contractSlots())->toBe(2)
        ->and($export->map($single->fresh(['activeContracts.service', 'activeContracts.direction']), 2))
        ->toBe(['Anne', 'Lambert', 'Urbanisme', null, 'Agent', null, null, null]);
});

it('keeps the header row when no agent matches', function (): void {
    $export = new EmployeeDirectoryExport(EmployeeRepository::activeAgentsQuery());

    expect($export->contractSlots())->toBe(1);
});

it('streams an xlsx file', function (): void {
    $employee = directoryAgent('Marie', 'Dupont');
    directoryContract($employee);

    $response = new EmployeeDirectoryExport(EmployeeRepository::activeAgentsQuery())
        ->downloadXlsx('annuaire.xlsx');

    expect($response->headers->get('Content-Type'))
        ->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))
        ->toContain('annuaire.xlsx');

    ob_start();
    $response->sendContent();
    $content = (string) ob_get_clean();

    expect($content)->toStartWith('PK');
});
