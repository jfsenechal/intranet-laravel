<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\EmployeeInformationPage;
use AcMarche\Hrm\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
});

it('shows the employee record matching the current user username', function (): void {
    $employee = Employee::factory()->create(['username' => 'jdoe']);
    $user = User::factory()->create(['username' => 'jdoe']);

    Livewire::actingAs($user)
        ->test(EmployeeInformationPage::class)
        ->assertOk()
        ->assertSet('employee.id', $employee->id)
        ->assertDontSee('Compte informatique');
});

it('has no employee when no username matches', function (): void {
    $user = User::factory()->create(['username' => 'unknown']);

    Livewire::actingAs($user)
        ->test(EmployeeInformationPage::class)
        ->assertOk()
        ->assertSet('employee', null);
});
