<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\ListAliasPage;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

use function Pest\Livewire\livewire;

const LISTS_DN = 'OU=LISTS,OU=MUSERS,dc=ad,DC=marche,DC=be';
const SERVICES_DN = 'OU=SERVICES,OU=MUSERS,dc=ad,DC=marche,DC=be';

/**
 * The base DNs come from .env in normal operation. Pinning them here keeps the tests from
 * depending on a machine's directory configuration, and keeps the OU scoping meaningful.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    config()->set('email-management.ldap.bases.lists', LISTS_DN);
    config()->set('email-management.ldap.bases.services', SERVICES_DN);

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
 * @param  array<int, string>  $members
 */
function makeList(string $cn, string $mail, array $members = [], ?string $description = null, string $dn = LISTS_DN): ListAliasLdap
{
    $entry = new ListAliasLdap;
    $entry->cn = $cn;
    $entry->mail = $mail;
    $entry->proxyAddresses = $members;

    if ($description !== null) {
        $entry->description = $description;
    }

    $entry->inside($dn)->save();

    return $entry;
}

describe('table', function (): void {
    it('lists the groups of the lists OU', function (): void {
        makeList('conseil', 'conseil@marche.be');
        makeList('college', 'college@marche.be');

        livewire(ListAliasPage::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords(['conseil', 'college']);
    });

    it('counts the members of a list', function (): void {
        makeList('conseil', 'conseil@marche.be', [
            'ana.aguirre@marche.be',
            'jean.dupont@marche.be',
            'luc.martin@marche.be',
        ]);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->assertTableColumnStateSet('members_count', 3, 'conseil');
    });

    it('shows the services OU when the filter selects it', function (): void {
        makeList('conseil', 'conseil@marche.be');
        makeList('informatique', 'informatique@marche.be', dn: SERVICES_DN);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->filterTable('ou', ListOuEnum::SERVICES->value)
            ->assertCanSeeTableRecords(['informatique'])
            ->assertCanNotSeeTableRecords(['conseil']);
    });

    it('finds a list by one of its members', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be']);
        makeList('college', 'college@marche.be', ['jean.dupont@marche.be']);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->searchTable('ana.aguirre@marche.be')
            ->assertCanSeeTableRecords(['conseil'])
            ->assertCanNotSeeTableRecords(['college']);
    });

    /**
     * The legacy searchList matched proxyAddresses only, so this search returned nothing.
     */
    it('finds a list by its own address', function (): void {
        makeList('conseil', 'conseil@marche.be');

        livewire(ListAliasPage::class)
            ->loadTable()
            ->searchTable('conseil@marche.be')
            ->assertCanSeeTableRecords(['conseil']);
    });
});

describe('members', function (): void {
    it('writes the new members to the directory', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be']);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->callAction(TestAction::make('editMembers')->table('conseil'), [
                'members' => ['jean.dupont@marche.be', 'luc.martin@marche.be'],
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $entry = ListAliasLdap::query()->in(LISTS_DN)->findBy('cn', 'conseil');

        expect($entry->getAttribute('proxyaddresses'))
            ->toBe(['jean.dupont@marche.be', 'luc.martin@marche.be']);
    });

    it('fills the form with the current members', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be']);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->mountAction(TestAction::make('editMembers')->table('conseil'))
            ->assertActionDataSet(['members' => ['ana.aguirre@marche.be']]);
    });

    it('refuses a member that is not an email address', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be']);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->callAction(TestAction::make('editMembers')->table('conseil'), [
                'members' => ['pas-une-adresse'],
            ])
            ->assertHasActionErrors();

        $entry = ListAliasLdap::query()->in(LISTS_DN)->findBy('cn', 'conseil');

        expect($entry->getAttribute('proxyaddresses'))->toBe(['ana.aguirre@marche.be']);
    });

    /**
     * Destructive, and the helper text promises it, so it is pinned rather than left to chance.
     */
    it('clears every member when saved empty', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be']);

        livewire(ListAliasPage::class)
            ->loadTable()
            ->callAction(TestAction::make('editMembers')->table('conseil'), ['members' => []])
            ->assertHasNoActionErrors();

        $entry = ListAliasLdap::query()->in(LISTS_DN)->findBy('cn', 'conseil');

        expect($entry->getAttribute('proxyaddresses'))->toBeNull();
    });
});

describe('description', function (): void {
    it('writes the new description to the directory', function (): void {
        makeList('conseil', 'conseil@marche.be', description: 'Ancienne description');

        livewire(ListAliasPage::class)
            ->loadTable()
            ->callAction(TestAction::make('editDescription')->table('conseil'), [
                'description' => 'Les membres du conseil communal',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $entry = ListAliasLdap::query()->in(LISTS_DN)->findBy('cn', 'conseil');

        expect($entry->getFirstAttribute('description'))->toBe('Les membres du conseil communal');
    });

    it('fills the form with the current description', function (): void {
        makeList('conseil', 'conseil@marche.be', description: 'Ancienne description');

        livewire(ListAliasPage::class)
            ->loadTable()
            ->mountAction(TestAction::make('editDescription')->table('conseil'))
            ->assertActionDataSet(['description' => 'Ancienne description']);
    });
});

describe('view', function (): void {
    /**
     * The modal body is rendered client-side, so this asserts the action mounts against the
     * right record rather than the text it will show, as the viewLdap test does.
     */
    it('opens on the list', function (): void {
        makeList('conseil', 'conseil@marche.be', ['ana.aguirre@marche.be'], 'Le conseil');

        livewire(ListAliasPage::class)
            ->loadTable()
            ->mountAction(TestAction::make('view')->table('conseil'))
            ->assertSuccessful()
            ->assertActionMounted(TestAction::make('view')->table('conseil'));
    });
});

describe('authorization', function (): void {
    it('denies the page to a user without ROLE_EMAIL_ADMIN', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ListAliasPage::class)->assertForbidden();
    });

    it('allows an administrator', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => true]));

        livewire(ListAliasPage::class)->assertSuccessful();
    });
});
