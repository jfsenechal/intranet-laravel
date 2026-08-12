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

it('creates the profile of an employee who has none', function (): void {
    $employee = Employee::factory()->create(['first_name' => 'Alice', 'last_name' => 'Martin']);

    Livewire::withQueryParams(['employee_id' => $employee->getKey()])
        ->test(CreateProfile::class)
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Profile::query()->where('employee_id', $employee->getKey())->first())
        ->not->toBeNull()
        ->first_name->toBe('Alice')
        ->last_name->toBe('Martin');
});

it('does not create a second profile when one appeared for the employee meanwhile', function (): void {
    $employee = Employee::factory()->create();

    $component = Livewire::withQueryParams(['employee_id' => $employee->getKey()])
        ->test(CreateProfile::class);

    $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

    $component
        ->call('create')
        ->assertRedirect(ViewProfile::getUrl(['record' => $profile->getKey()], panel: 'agent-panel'));

    expect(Profile::query()->where('employee_id', $employee->getKey())->count())->toBe(1);
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
