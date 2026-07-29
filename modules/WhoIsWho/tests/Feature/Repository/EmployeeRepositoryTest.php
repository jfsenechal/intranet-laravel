<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;

/**
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $contractAttributes
 */
function directoryEmployee(array $attributes = [], array $contractAttributes = []): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
        ...$attributes,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'start_date' => now()->subMonth(),
        'end_date' => null,
        ...$contractAttributes,
    ]);

    return $employee;
}

it('lists an agent with a running contract', function (): void {
    $employee = directoryEmployee();

    expect(EmployeeRepository::activeAgents()->pluck('id'))->toContain($employee->id);
});

it('excludes an agent without any contract', function (): void {
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
    ]);

    expect(EmployeeRepository::activeAgents()->pluck('id'))->not->toContain($employee->id);
});

it('excludes an agent whose contract is closed, suspended or expired', function (array $contractAttributes): void {
    $employee = directoryEmployee([], $contractAttributes);

    expect(EmployeeRepository::activeAgents()->pluck('id'))->not->toContain($employee->id);
})->with([
    'closed' => [['is_closed' => true]],
    'suspended' => [['is_suspended' => true]],
    'expired' => [['end_date' => now()->subDay()]],
    'not started yet' => [['start_date' => now()->addWeek()]],
]);

it('excludes the non-person records of the hrm employee table', function (string $lastName): void {
    $employee = directoryEmployee(['last_name' => $lastName]);

    expect(EmployeeRepository::activeAgents()->pluck('id'))->not->toContain($employee->id);
})->with([
    'A - SYNDICAT CGSP',
    'A-PROCEDURE-',
    '@ RH',
    'PROCEDURE',
    'DMFA Ville',
]);

it('excludes a non-person record marked on the first name', function (): void {
    $employee = directoryEmployee(['first_name' => 'DMFA Ville', 'last_name' => 'ADMINISTRATION']);

    expect(EmployeeRepository::activeAgents()->pluck('id'))->not->toContain($employee->id);
});

it('keeps an agent whose name merely starts with the letter a', function (): void {
    $employee = directoryEmployee(['last_name' => 'ADAM', 'first_name' => 'Marthe']);

    expect(EmployeeRepository::activeAgents()->pluck('id'))->toContain($employee->id);
});

it('excludes non-person records from the search results', function (): void {
    $employee = directoryEmployee(['last_name' => 'A - SYNDICAT CGSP']);

    expect(EmployeeRepository::search('SYNDICAT')->pluck('id'))->not->toContain($employee->id);
});

it('does not list a service whose only agents are non-person records', function (): void {
    $service = Service::factory()->create();

    directoryEmployee(['last_name' => 'A - CESI'], ['service_id' => $service->id]);

    expect(EmployeeRepository::servicesWithAgents()->pluck('id'))->not->toContain($service->id);
});

it('lists a service that has a real agent', function (): void {
    $service = Service::factory()->create();

    directoryEmployee([], ['service_id' => $service->id]);

    expect(EmployeeRepository::servicesWithAgents()->pluck('id'))->toContain($service->id);
});
