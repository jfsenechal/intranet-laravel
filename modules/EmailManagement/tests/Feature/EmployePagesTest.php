<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\ListEmployePage;
use AcMarche\EmailManagement\Filament\Pages\ViewEmployePage;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Schema;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    config()->set('email-management.ldap.bases.lists', 'OU=LISTS,OU=MUSERS,dc=ad,DC=marche,DC=be');
    config()->set('email-management.ldap.bases.services', 'OU=SERVICES,OU=MUSERS,dc=ad,DC=marche,DC=be');

    DirectoryEmulator::setup('default');

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function makeEmployeEntry(array $attributes = []): void
{
    $entry = new EmployeLdap;

    foreach (['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'givenName' => 'Alice', 'sn' => 'Martin', ...$attributes] as $name => $value) {
        $entry->{$name} = $value;
    }

    $entry->inside(config('email-management.ldap.bases.employes'))->save();
}

/**
 * @param  array<int, string>  $members
 */
function makeListAlias(string $cn, ?string $mail, array $members, string $dn): void
{
    $entry = new ListAliasLdap;
    $entry->cn = $cn;

    if ($mail !== null) {
        $entry->mail = $mail;
    }

    $entry->proxyAddresses = $members;
    $entry->inside($dn)->save();
}

function employeMail(string $samAccountName): ?string
{
    return app(EmployeLdapRepository::class)->getEntry($samAccountName)->getFirstAttribute('mail');
}

describe('list', function (): void {
    beforeEach(function (): void {
        makeEmployeEntry(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);
        makeEmployeEntry(['cn' => 'Elodie Etienne', 'samaccountname' => 'eetienne', 'sn' => 'Étienne', 'mail' => 'elodie.etienne@ac.marche.be']);
        makeEmployeEntry(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'gathy', 'mail' => 'bea.gathy@ac.marche.be']);
    });

    it('lists the accounts of the directory by last name', function (): void {
        livewire(ListEmployePage::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords(['eetienne', 'bgathy', 'amartin'], inOrder: true);
    });

    it('sorts on a chosen column and direction', function (): void {
        livewire(ListEmployePage::class)
            ->loadTable()
            ->sortTable('samaccountname', 'desc')
            ->assertCanSeeTableRecords(['eetienne', 'bgathy', 'amartin'], inOrder: true)
            ->sortTable('samaccountname', 'asc')
            ->assertCanSeeTableRecords(['amartin', 'bgathy', 'eetienne'], inOrder: true);
    });

    it('leaves out an entry without a samaccountname', function (): void {
        makeEmployeEntry(['cn' => 'Sans Compte', 'samaccountname' => null, 'sn' => 'Compte']);

        livewire(ListEmployePage::class)
            ->loadTable()
            ->assertCountTableRecords(3);
    });

    it('searches any listed field, ignoring case', function (string $search): void {
        livewire(ListEmployePage::class)
            ->loadTable()
            ->searchTable($search)
            ->assertCanSeeTableRecords(['bgathy'])
            ->assertCanNotSeeTableRecords(['amartin', 'eetienne']);
    })->with(['GATHY', 'bea.gathy@', 'BGATHY']);

    it('opens the account from its row', function (): void {
        livewire(ListEmployePage::class)
            ->loadTable()
            ->assertActionHasUrl(
                TestAction::make('view')->table('bgathy'),
                ViewEmployePage::getUrl(['samaccountname' => 'bgathy']),
            );
    });
});

describe('view', function (): void {
    it('shows the account read from the directory', function (): void {
        makeEmployeEntry(['mail' => 'alice.martin@ac.marche.be', 'telephoneNumber' => '+3284326991']);

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->assertSuccessful()
            ->assertSee('alice.martin@ac.marche.be')
            ->assertSee('+3284326991');
    });

    it('answers 404 for an account absent from the directory', function (): void {
        livewire(ViewEmployePage::class, ['samaccountname' => 'fantome'])->assertNotFound();
    });

    it('keeps its account out of reach of the client', function (): void {
        makeEmployeEntry();

        expect(fn () => livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])->set('employe.samaccountname', 'autre'))
            ->toThrow(CannotUpdateLockedPropertyException::class);
    });

    it('resolves its route from the samaccountname', function (): void {
        makeEmployeEntry();

        $this->get(ViewEmployePage::getUrl(['samaccountname' => 'amartin']))->assertOk();
    });
});

describe('member of lists', function (): void {
    it('shows the lists and services delivering to the address', function (): void {
        makeEmployeEntry(['mail' => 'alice.martin@marche.be']);

        makeListAlias('conseil', 'conseil@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.lists'));
        makeListAlias('informatique', 'informatique@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.services'));
        makeListAlias('college', 'college@marche.be', ['jean.dupont@marche.be'], config('email-management.ldap.bases.lists'));

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->assertSuccessful()
            ->assertSee('conseil@marche.be')
            ->assertSee('informatique@marche.be')
            ->assertDontSee('college@marche.be');
    });

    /**
     * A group with no mail of its own is still worth showing, so it falls back to its cn.
     */
    it('names a group without an address by its cn', function (): void {
        makeEmployeEntry(['mail' => 'alice.martin@marche.be']);

        makeListAlias('conseil', null, ['alice.martin@marche.be'], config('email-management.ldap.bases.lists'));

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->assertSuccessful()
            ->assertSee('conseil');
    });

    it('renders the page when the employe has no address', function (): void {
        makeEmployeEntry();

        makeListAlias('conseil', 'conseil@marche.be', ['alice.martin@marche.be'], config('email-management.ldap.bases.lists'));

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->assertSuccessful()
            ->assertDontSee('conseil@marche.be');
    });
});

describe('header actions', function (): void {
    it('joins the local part to the chosen domain, then reloads the page', function (): void {
        makeEmployeEntry();

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->callAction('createEmail', [
                'mail' => 'alice.martin',
                'extension' => EmailExtensionEnum::EXTENSION_CPAS->value,
            ])
            ->assertHasNoActionErrors()
            ->assertRedirect(ViewEmployePage::getUrl(['samaccountname' => 'amartin']));

        expect(employeMail('amartin'))->toBe('alice.martin@cpas.marche.be');
    });

    it('splits an existing address back across the two fields', function (): void {
        makeEmployeEntry(['mail' => 'alice.martin@cpas.marche.be']);

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->mountAction('createEmail')
            ->assertActionDataSet([
                'mail' => 'alice.martin',
                // The select casts the state to the enum case, which is why the action
                // normalises it back to a string before building the address.
                'extension' => EmailExtensionEnum::EXTENSION_CPAS,
            ]);
    });

    it('suggests an address from the name of an employe without one', function (): void {
        makeEmployeEntry();

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->mountAction('createEmail')
            ->assertActionDataSet(['mail' => 'alice.martin']);
    });

    it('refuses a local part containing a domain', function (): void {
        makeEmployeEntry();

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->callAction('createEmail', [
                'mail' => 'alice.martin@ac.marche.be',
                'extension' => EmailExtensionEnum::EXTENSION_AC->value,
            ])
            ->assertHasActionErrors(['mail']);
    });

    it('writes the quota to the directory', function (): void {
        makeEmployeEntry();

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->callAction('changeQuota', ['quota' => 2048])
            ->assertHasNoActionErrors();

        $repository = app(EmployeLdapRepository::class);

        expect($repository->getQuota($repository->getEntry('amartin')))->toBe(2048.0);
    });

    it('mounts every mailbox action', function (string $action): void {
        makeEmployeEntry(['mail' => 'alice.martin@ac.marche.be']);

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])
            ->mountAction($action)
            ->assertSuccessful()
            ->assertActionMounted($action);
    })->with(['createEmail', 'changeQuota', 'vacation']);
});

describe('authorization', function (): void {
    it('denies the list page to a user without ROLE_EMAIL_ADMIN', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ListEmployePage::class)->assertForbidden();
    });

    it('denies the view page to a user without ROLE_EMAIL_ADMIN', function (): void {
        makeEmployeEntry();
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ViewEmployePage::class, ['samaccountname' => 'amartin'])->assertForbidden();
    });

    /**
     * Filament checks no policy on array records, and access is only checked when a page opens.
     * A user who loses the role with the page open must not keep its write actions.
     */
    it('hides the actions from a user who lost the role after the page opened', function (string $action): void {
        makeEmployeEntry();

        $component = livewire(ViewEmployePage::class, ['samaccountname' => 'amartin']);

        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        $component->assertActionHidden($action);
    })->with(['createEmail', 'changeQuota', 'vacation']);
});

it('keeps no copy of the staff directory', function (): void {
    expect(Schema::hasTable('employes'))->toBeFalse()
        ->and(Schema::connection('maria-email-management')->hasTable('employes'))->toBeFalse();
});
