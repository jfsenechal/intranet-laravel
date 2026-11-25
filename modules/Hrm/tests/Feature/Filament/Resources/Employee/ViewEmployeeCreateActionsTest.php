<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('mounts the header create action without resolving its relationships on the employee', function (string $actionName): void {
    $employee = Employee::factory()->create();

    Livewire::test(ViewEmployee::class, ['record' => $employee->getRouteKey()])
        ->mountAction($actionName)
        ->assertActionMounted($actionName)
        ->assertHasNoErrors();
})->with(['addDiploma', 'addEvaluation', 'addValorization', 'addInternship', 'addApplication']);

it('creates an internship for the viewed employee from the header action', function (): void {
    $employee = Employee::factory()->create();

    Livewire::test(ViewEmployee::class, ['record' => $employee->getRouteKey()])
        ->callAction('addInternship', [
            'start_date' => '2026-01-05',
            'end_date' => '2026-02-05',
        ])
        ->assertHasNoActionErrors();

    expect($employee->internships()->count())->toBe(1);
});
