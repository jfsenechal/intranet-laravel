<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\EditEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ListEmployes;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ViewEmploye;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
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

/**
 * @param  array<int, string>  $members
 */
function makeListAlias(string $cn, string $mail, array $members, string $dn): void
{
    $entry = new ListAliasLdap;
    $entry->cn = $cn;
    $entry->mail = $mail;
    $entry->proxyAddresses = $members;
    $entry->inside($dn)->save();
}

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
    /**
     * The mirror and the form carry the same identity attributes: everything the form edits is
     * pushed by EmployeHandler::updateEmploye, and nothing is mirrored that cannot be edited.
     * A field on one side but not the other would save to the mirror and silently never reach
     * the directory, so this holds the two lists together.
     */
    it('exposes every identity field the mirror carries', function (): void {
        $component = livewire(EditEmploye::class, ['record' => Employe::factory()->create()->id]);

        foreach (Employe::LDAP_IDENTITY_ATTRIBUTES as $attribute) {
            $component->assertFormFieldExists($attribute);
        }
    });

    /**
     * A factory-made employe has to be saveable through its own form. It was not: the phone
     * numbers faker produces sometimes carry an extension, which the ->tel() rule rejects, and
     * the save test below failed at random depending on the number drawn. Enough samples are
     * drawn here that a factory generating rejected numbers again fails every run.
     */
    it('generates a phone number the form accepts', function (): void {
        $numbers = Employe::factory()
            ->count(50)
            ->make()
            ->pluck('telephoneNumber')
            ->filter();

        // Filament's default rule behind TextInput::tel().
        expect($numbers)->each->toMatch('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/');
    });

    it('saves the new identity fields to the mirror', function (): void {
        DirectoryEmulator::setup('default');

        $employe = Employe::factory()->create(['samaccountname' => 'amartin']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Alice Martin';
        $ldapEntry->samaccountname = 'amartin';
        $ldapEntry->sn = 'Martin';
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

describe('member of lists', function (): void {
    /**
     * The base DNs come from .env in normal operation. Pinning them keeps the OU scoping
     * meaningful without depending on a machine's directory configuration.
     */
    beforeEach(function (): void {
        config()->set('email-management.ldap.bases.lists', 'OU=LISTS,OU=MUSERS,dc=ad,DC=marche,DC=be');
        config()->set('email-management.ldap.bases.services', 'OU=SERVICES,OU=MUSERS,dc=ad,DC=marche,DC=be');

        DirectoryEmulator::setup('default');
    });

    afterEach(function (): void {
        DirectoryEmulator::tearDown();
    });

    it('shows the lists and services delivering to the address', function (): void {
        $employe = Employe::factory()->create(['mail' => 'alice.martin@marche.be']);

        makeListAlias('conseil', 'conseil@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.lists'));
        makeListAlias('informatique', 'informatique@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.services'));
        makeListAlias('college', 'college@marche.be', ['jean.dupont@marche.be'], config('email-management.ldap.bases.lists'));

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->assertSuccessful()
            ->assertSee('conseil@marche.be')
            ->assertSee('informatique@marche.be')
            ->assertDontSee('college@marche.be');
    });

    /**
     * A group with no mail of its own is still worth showing, so it falls back to its cn.
     */
    it('names a group without an address by its cn', function (): void {
        $employe = Employe::factory()->create(['mail' => 'alice.martin@marche.be']);

        $entry = new ListAliasLdap;
        $entry->cn = 'conseil';
        $entry->proxyAddresses = ['alice.martin@marche.be'];
        $entry->inside(config('email-management.ldap.bases.lists'))->save();

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->assertSuccessful()
            ->assertSee('conseil');
    });

    it('renders the page when the employe has no address', function (): void {
        $employe = Employe::factory()->create(['mail' => null]);

        makeListAlias('conseil', 'conseil@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.lists'));

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->assertSuccessful()
            ->assertDontSee('conseil@marche.be');
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
        $employe = Employe::factory()->create(['samaccountname' => 'amartin']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Alice Martin';
        $ldapEntry->samaccountname = 'amartin';
        $ldapEntry->givenName = 'Alice';
        $ldapEntry->sn = 'Martin';
        $ldapEntry->mail = 'alice.martin@ac.marche.be';
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

    it('joins the local part to the chosen domain', function (): void {
        $employe = Employe::factory()->create(['samaccountname' => 'amartin', 'mail' => null]);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Alice Martin';
        $ldapEntry->samaccountname = 'amartin';
        $ldapEntry->sn = 'Martin';
        $ldapEntry->inside(config('email-management.ldap.bases.employes'))->save();

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->callAction('createEmail', [
                'mail' => 'alice.martin',
                'extension' => EmailExtensionEnum::EXTENSION_CPAS->value,
            ])
            ->assertHasNoActionErrors();

        expect($employe->refresh()->mail)->toBe('alice.martin@cpas.marche.be');
    });

    it('splits an existing address back across the two fields', function (): void {
        $employe = Employe::factory()->create([
            'samaccountname' => 'amartin',
            'mail' => 'alice.martin@cpas.marche.be',
        ]);

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->mountAction('createEmail')
            ->assertActionDataSet([
                'mail' => 'alice.martin',
                // The select casts the state to the enum case, which is why the action
                // normalises it back to a string before building the address.
                'extension' => EmailExtensionEnum::EXTENSION_CPAS,
            ]);
    });

    it('refuses a local part containing a domain', function (): void {
        $employe = Employe::factory()->create(['samaccountname' => 'amartin', 'mail' => null]);

        livewire(ViewEmploye::class, ['record' => $employe->id])
            ->callAction('createEmail', [
                'mail' => 'alice.martin@ac.marche.be',
                'extension' => EmailExtensionEnum::EXTENSION_AC->value,
            ])
            ->assertHasActionErrors(['mail']);
    });

    it('mounts every mailbox action', function (string $action): void {
        $employe = Employe::factory()->create(['samaccountname' => 'amartin']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = 'Alice Martin';
        $ldapEntry->samaccountname = 'amartin';
        $ldapEntry->sn = 'Martin';
        $ldapEntry->mail = 'alice.martin@ac.marche.be';
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
