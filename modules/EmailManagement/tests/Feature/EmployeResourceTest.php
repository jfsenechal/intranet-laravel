<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\CreateEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\EditEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ListEmployes;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ViewEmploye;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);
});

describe('pages render', function (): void {
    it('renders the list page', function (): void {
        $employes = Employe::factory(3)->create();

        livewire(ListEmployes::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords($employes);
    });

    it('renders the create page', function (): void {
        livewire(CreateEmploye::class)->assertSuccessful();
    });

    it('renders the view page', function (): void {
        $employe = Employe::factory()->create();

        livewire(ViewEmploye::class, ['record' => $employe->id])->assertSuccessful();
    });

    it('renders the edit page', function (): void {
        $employe = Employe::factory()->create();

        livewire(EditEmploye::class, ['record' => $employe->id])->assertSuccessful();
    });
});

describe('table', function (): void {
    it('searches by email', function (): void {
        $employes = Employe::factory(3)->create();

        livewire(ListEmployes::class)
            ->loadTable()
            ->searchTable($employes->first()->mail)
            ->assertCanSeeTableRecords($employes->take(1))
            ->assertCanNotSeeTableRecords($employes->skip(1));
    });

    it('searches by samaccountname', function (): void {
        $employes = Employe::factory(3)->create();

        livewire(ListEmployes::class)
            ->loadTable()
            ->searchTable($employes->first()->samaccountname)
            ->assertCanSeeTableRecords($employes->take(1));
    });
});

describe('create validation', function (): void {
    it('requires nom, identifiant, email and password', function (): void {
        livewire(CreateEmploye::class)
            ->fillForm([
                'sn' => null,
                'samaccountname' => null,
                'mail' => null,
                'password' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'sn' => 'required',
                'samaccountname' => 'required',
                'mail' => 'required',
                'password' => 'required',
            ]);
    });

    it('rejects a malformed email', function (): void {
        livewire(CreateEmploye::class)
            ->fillForm(['mail' => 'not-an-email'])
            ->call('create')
            ->assertHasFormErrors(['mail' => 'email']);
    });

    it('rejects a password shorter than 12 characters', function (): void {
        livewire(CreateEmploye::class)
            ->fillForm([
                'password' => 'Ab1!efgh',
                'password_confirmation' => 'Ab1!efgh',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    });

    it('rejects a password that is not confirmed', function (): void {
        livewire(CreateEmploye::class)
            ->fillForm([
                'password' => 'Str0ng!Passw0rd#2026',
                'password_confirmation' => 'Different!Passw0rd#2026',
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    });

    it('rejects an identifiant that already exists locally', function (): void {
        $existing = Employe::factory()->create();

        livewire(CreateEmploye::class)
            ->fillForm(['samaccountname' => $existing->samaccountname])
            ->call('create')
            ->assertHasFormErrors(['samaccountname' => 'unique']);
    });
});

describe('authorization', function (): void {
    it('denies the list page to a user without ROLE_EMAIL_ADMIN', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ListEmployes::class)->assertForbidden();
    });
});
