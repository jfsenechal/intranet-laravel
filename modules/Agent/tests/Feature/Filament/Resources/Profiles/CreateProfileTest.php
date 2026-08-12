<?php

declare(strict_types=1);

use AcMarche\Agent\Enums\RolesEnum;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\CreateProfile;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ViewProfile;
use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('agent-panel'));
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT_ADMIN->value]);
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->adminUser->roles()->attach($adminRole);
    $this->actingAs($this->adminUser);
});

it('displays the job functions of the active contracts of the employee', function (): void {
    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->getKey(),
        'job_title' => 'Ouvrier communal',
        'is_suspended' => false,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->getKey(),
        'job_title' => 'Chef d\'équipe',
        'is_closed' => true,
    ]);

    Livewire::withQueryParams(['employee_id' => $employee->getKey()])
        ->test(CreateProfile::class)
        ->assertSee('Fonctions (contrats actifs)')
        ->assertSee('Ouvrier communal')
        ->assertDontSee('Chef d\'équipe');
});

it('redirects to the existing profile when the employee already has one', function (): void {
    $employee = Employee::factory()->create();
    $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

    Livewire::withQueryParams(['employee_id' => $employee->getKey()])
        ->test(CreateProfile::class)
        ->assertRedirect(ViewProfile::getUrl(['record' => $profile->getKey()], panel: 'agent-panel'));
});

it('shows a placeholder when the employee has no job function', function (): void {
    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->getKey(),
        'job_title' => null,
        'is_suspended' => false,
    ]);

    Livewire::withQueryParams(['employee_id' => $employee->getKey()])
        ->test(CreateProfile::class)
        ->assertSee('Fonctions (contrats actifs)')
        ->assertSee('—');
});
