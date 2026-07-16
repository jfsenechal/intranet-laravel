<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\EditEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ListEmployes;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ViewEmploye;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

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

describe('edit form', function (): void {
    it('exposes every identity field the mirror carries', function (): void {
        $employe = Employe::factory()->create();

        $component = livewire(EditEmploye::class, ['record' => $employe->id]);

        foreach (Employe::LDAP_IDENTITY_ATTRIBUTES as $attribute) {
            $component->assertFormFieldExists($attribute);
        }
    });

    it('saves the new identity fields to the mirror', function (): void {
        DirectoryEmulator::setup('default');

        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Ana Aguirre';
        $ldapEntry->samaccountname = 'aaguirre';
        $ldapEntry->sn = 'Aguirre';
        $ldapEntry->inside(config('email-management.ldap.bases.employes'))->save();

        livewire(EditEmploye::class, ['record' => $employe->id])
            ->fillForm([
                'title' => 'Attachée',
                'company' => 'AC Marche',
                'l' => 'Marche-en-Famenne',
                'postalCode' => '6900',
                'mobile' => '+32477320320',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($employe->refresh())
            ->title->toBe('Attachée')
            ->company->toBe('AC Marche')
            ->l->toBe('Marche-en-Famenne')
            ->postalCode->toBe('6900')
            ->mobile->toBe('+32477320320');

        DirectoryEmulator::tearDown();
    });
});

describe('ldap header actions', function (): void {
    beforeEach(function (): void {
        DirectoryEmulator::setup('default');
    });

    afterEach(function (): void {
        DirectoryEmulator::tearDown();
    });

    it('shows the directory attributes of the employe', function (): void {
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Ana Aguirre';
        $ldapEntry->samaccountname = 'aaguirre';
        $ldapEntry->givenName = 'Ana';
        $ldapEntry->sn = 'Aguirre';
        $ldapEntry->mail = 'ana.aguirre@ac.marche.be';
        $ldapEntry->inside(config('email-management.ldap.bases.employes'))->save();

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->mountAction('viewLdap')
            ->assertSuccessful()
            ->assertActionMounted('viewLdap');
    });

    it('reports an employe that is absent from the directory', function (): void {
        $employe = Employe::factory()->create(['samaccountname' => 'inconnu']);

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->mountAction('viewLdap')
            ->assertSuccessful()
            ->assertActionMounted('viewLdap');
    });

    it('mounts every mailbox action', function (string $action): void {
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Ana Aguirre';
        $ldapEntry->samaccountname = 'aaguirre';
        $ldapEntry->sn = 'Aguirre';
        $ldapEntry->mail = 'ana.aguirre@ac.marche.be';
        $ldapEntry->inside(config('email-management.ldap.bases.employes'))->save();

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->mountAction($action)
            ->assertSuccessful()
            ->assertActionMounted($action);
    })->with(['createEmail', 'changeQuota', 'changeAlias', 'vacation']);
});

describe('authorization', function (): void {
    it('denies the list page to a user without ROLE_EMAIL_ADMIN', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ListEmployes::class)->assertForbidden();
    });
});
