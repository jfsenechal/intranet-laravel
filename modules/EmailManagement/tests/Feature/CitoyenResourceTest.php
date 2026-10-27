<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\EditCitoyen;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ListCitoyens;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ViewCitoyen;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Policies\CitoyenPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
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
        $citoyens = Citoyen::factory(3)->create();

        livewire(ListCitoyens::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords($citoyens);
    });

    it('renders the view page', function (): void {
        $citoyen = Citoyen::factory()->create();

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])->assertSuccessful();
    });

    it('renders the edit page', function (): void {
        $citoyen = Citoyen::factory()->create();

        livewire(EditCitoyen::class, ['record' => $citoyen->id])->assertSuccessful();
    });
});

describe('sensitive data', function (): void {
    it('never renders the password or token hashes but shows the national number', function (): void {
        $citoyen = Citoyen::factory()->create([
            'userPassword' => '{SSHA}secretHashValue123',
            'auth_token' => 'secretAuthTokenValue456',
            'employeeNumber' => '62011108917',
        ]);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->assertSee('62011108917')
            ->assertDontSee('secretHashValue123')
            ->assertDontSee('secretAuthTokenValue456');
    });

    it('keeps the national number and recovery fields out of the table', function (): void {
        livewire(ListCitoyens::class)
            ->assertTableColumnDoesNotExist('employeeNumber')
            ->assertTableColumnDoesNotExist('recovery_email')
            ->assertTableColumnDoesNotExist('recovery_phone');
    });

    it('hides the hashes when serialized', function (): void {
        $citoyen = Citoyen::factory()->create(['legacy_password' => '{SHA}legacy']);
        $citoyen->forceFill(['remember_token' => 'remember'])->save();

        expect($citoyen->refresh()->toArray())
            ->not->toHaveKeys(['userPassword', 'legacy_password', 'auth_token', 'remember_token']);
    });
});

describe('table', function (): void {
    it('searches by email', function (): void {
        $citoyens = Citoyen::factory(3)->create();

        livewire(ListCitoyens::class)
            ->loadTable()
            ->searchTable($citoyens->first()->mail)
            ->assertCanSeeTableRecords($citoyens->take(1))
            ->assertCanNotSeeTableRecords($citoyens->skip(1));
    });

    it('searches by uid', function (): void {
        $citoyens = Citoyen::factory(3)->create();

        livewire(ListCitoyens::class)
            ->loadTable()
            ->searchTable($citoyens->last()->uid)
            ->assertCanSeeTableRecords($citoyens->take(-1))
            ->assertCanNotSeeTableRecords($citoyens->take(2));
    });

    it('filters on the last connection before a date', function (): void {
        $old = Citoyen::factory()->create(['last_connection' => '2026-01-10']);
        $recent = Citoyen::factory()->create(['last_connection' => '2026-06-01']);
        $never = Citoyen::factory()->create(['last_connection' => null]);

        livewire(ListCitoyens::class)
            ->loadTable()
            ->filterTable('last_connection', ['connected_before' => '2026-03-01'])
            ->assertCanSeeTableRecords([$old])
            ->assertCanNotSeeTableRecords([$recent, $never]);
    });

    it('filters on charter acceptance', function (): void {
        $accepted = Citoyen::factory()->create();
        $accepted->forceFill(['charter_accepted_at' => now()])->save();
        $pending = Citoyen::factory()->create();

        livewire(ListCitoyens::class)
            ->loadTable()
            ->filterTable('charter_accepted_at', true)
            ->assertCanSeeTableRecords([$accepted])
            ->assertCanNotSeeTableRecords([$pending])
            ->filterTable('charter_accepted_at', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$accepted]);
    });
});

describe('edit form', function (): void {
    it('saves the editable fields', function (): void {
        $citoyen = Citoyen::factory()->create();

        livewire(EditCitoyen::class, ['record' => $citoyen->id])
            ->fillForm([
                'sn' => 'Dupont',
                'postalCode' => '6990',
                'recovery_email' => 'jean@example.be',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($citoyen->refresh())
            ->sn->toBe('Dupont')
            ->postalCode->toBe('6990')
            ->recovery_email->toBe('jean@example.be');
    });

    /**
     * The mailbox identity is not in the form, so values smuggled into the form state for
     * those keys must not reach the database.
     */
    it('never writes the read-only mailbox columns', function (): void {
        $citoyen = Citoyen::factory()->create([
            'mail' => 'alice.martin@marche.be',
            'uid' => 'alice.martin',
            'gosaMailQuota' => 250,
            'userPassword' => '{SSHA}original',
        ]);

        livewire(EditCitoyen::class, ['record' => $citoyen->id])
            ->fillForm([
                'sn' => 'Nouveau',
                'mail' => 'hack@example.be',
                'uid' => 'hack',
                'gosaMailQuota' => 9999,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $citoyen->refresh();

        expect($citoyen)
            ->sn->toBe('Nouveau')
            ->mail->toBe('alice.martin@marche.be')
            ->uid->toBe('alice.martin')
            ->gosaMailQuota->toBe(250.0)
            ->and($citoyen->getAttributes()['userPassword'])->toBe('{SSHA}original');
    });

    it('saves an unchanged record without touching it', function (): void {
        $this->travelTo(now()->subDay());
        $citoyen = Citoyen::factory()->create();
        $updatedAt = $citoyen->updated_at->toDateTimeString();
        $this->travelBack();

        livewire(EditCitoyen::class, ['record' => $citoyen->id])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($citoyen->refresh()->updated_at->toDateTimeString())->toBe($updatedAt);
    });

    it('validates the editable fields', function (string $field, string $value, ?string $rule): void {
        $component = livewire(EditCitoyen::class, ['record' => Citoyen::factory()->create()->id])
            ->fillForm([$field => $value])
            ->call('save');

        if ($rule === null) {
            $component->assertHasNoFormErrors();
        } else {
            $component->assertHasFormErrors([$field => $rule]);
        }
    })->with([
        'invalid recovery email' => ['recovery_email', 'not-an-email', 'email'],
        'valid recovery email' => ['recovery_email', 'a@b.be', null],
        'sn too long' => ['sn', str_repeat('a', 256), 'max'],
        'sn at the limit' => ['sn', str_repeat('a', 255), null],
        'postal code too long' => ['postalCode', str_repeat('1', 256), 'max'],
    ]);
});

describe('authorization', function (): void {
    it('forbids the list page to a user without the role', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(ListCitoyens::class)->assertForbidden();
    });

    it('has no create page', function (): void {
        expect(CitoyenResource::hasPage('create'))->toBeFalse();
    });

    it('offers no delete action on the edit page', function (): void {
        livewire(EditCitoyen::class, ['record' => Citoyen::factory()->create()->id])
            ->assertActionDoesNotExist(DeleteAction::class);
    });

    it('denies create and delete even to a super administrator', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $citoyen = Citoyen::factory()->create();
        $policy = new CitoyenPolicy;

        expect($policy->create($admin))->toBeFalse()
            ->and($policy->delete($admin, $citoyen))->toBeFalse()
            ->and($policy->viewAny($admin))->toBeTrue()
            ->and($policy->update($admin, $citoyen))->toBeTrue();
    });
});
