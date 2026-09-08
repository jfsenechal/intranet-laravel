<?php

declare(strict_types=1);

use AcMarche\Ad\Filament\Pages\EmployeesWithoutProfessionalEmail;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('ad-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

function adEmployee(array $attributes = [], bool $withActiveContract = true): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        ...$attributes,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => ! $withActiveContract,
        'is_suspended' => false,
        'end_date' => $withActiveContract ? now()->addMonth() : now()->subDay(),
    ]);

    return $employee;
}

it('can render the page', function (): void {
    livewire(EmployeesWithoutProfessionalEmail::class)
        ->assertOk();
});

it('lists an eligible agent without a professional email but with a private one', function (): void {
    $employee = adEmployee([
        'professional_email' => null,
        'private_email' => 'bob.dupont@example.com',
    ]);

    livewire(EmployeesWithoutProfessionalEmail::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$employee]);
});

it('does not list an agent who has a professional email', function (): void {
    $employee = adEmployee([
        'professional_email' => 'alice.martin@marche.be',
        'private_email' => 'alice@example.com',
    ]);

    livewire(EmployeesWithoutProfessionalEmail::class)
        ->loadTable()
        ->assertCanNotSeeTableRecords([$employee]);
});

it('does not list an agent whose private email is missing or empty', function (?string $privateEmail): void {
    $employee = adEmployee([
        'professional_email' => null,
        'private_email' => $privateEmail,
    ]);

    livewire(EmployeesWithoutProfessionalEmail::class)
        ->loadTable()
        ->assertCanNotSeeTableRecords([$employee]);
})->with([null, '']);

it('does not list an employee who is not an eligible agent', function (): void {
    $student = adEmployee([
        'status' => StatusEnum::STUDENT->value,
        'professional_email' => null,
        'private_email' => 'student@example.com',
    ]);

    $withoutActiveContract = adEmployee([
        'professional_email' => null,
        'private_email' => 'inactive@example.com',
    ], withActiveContract: false);

    livewire(EmployeesWithoutProfessionalEmail::class)
        ->loadTable()
        ->assertCanNotSeeTableRecords([$student, $withoutActiveContract]);
});

it('is not accessible without the ad admin role', function (): void {
    auth()->user()->update(['is_administrator' => false]);

    expect(EmployeesWithoutProfessionalEmail::canAccess())->toBeFalse();
});

it('has column', function (string $column): void {
    livewire(EmployeesWithoutProfessionalEmail::class)
        ->assertTableColumnExists($column);
})->with(['last_name', 'first_name', 'private_email']);
