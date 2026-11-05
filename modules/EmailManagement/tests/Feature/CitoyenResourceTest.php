<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\CreateCitoyen;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\EditCitoyen;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ListCitoyens;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages\ViewCitoyen;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use AcMarche\EmailManagement\Policies\CitoyenPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
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

describe('quota', function (): void {
    it('shows the quota of the login on the view page', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['quota_bytes' => 777]);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])->assertSee('777');
    });

    it('sorts the table by the quota of the login', function (): void {
        $small = Citoyen::factory()->create(['sn' => 'Adam']);
        $large = Citoyen::factory()->create(['sn' => 'Zimmer']);
        $small->virtualUser->update(['quota_bytes' => 100]);
        $large->virtualUser->update(['quota_bytes' => 900]);

        livewire(ListCitoyens::class)
            ->toggleAllTableColumns()
            ->loadTable()
            ->sortTable('virtualUser.quota_bytes', 'desc')
            ->assertCanSeeTableRecords([$large, $small], inOrder: true);
    });
});

describe('active', function (): void {
    it('shows whether the login is active in the table', function (): void {
        $inactive = Citoyen::factory()->create();
        $inactive->virtualUser->update(['active' => false]);

        livewire(ListCitoyens::class)
            ->loadTable()
            ->assertTableColumnStateSet('virtualUser.active', false, $inactive);
    });

    it('renders the view page for an inactive login', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['active' => false]);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])->assertSuccessful();
    });

    it('deactivates the login and leaves the rest of it untouched', function (): void {
        $citoyen = Citoyen::factory()->create();
        $virtualUser = $citoyen->virtualUser;
        $virtualUser->update(['userPassword' => '{SSHA}secret', 'quota_bytes' => 777]);

        livewire(EditCitoyen::class, ['record' => $citoyen->id])
            ->assertSchemaStateSet(['virtualUser.active' => true])
            ->fillForm(['virtualUser' => ['active' => false]])
            ->call('save')
            ->assertHasNoFormErrors();

        $virtualUser->refresh();

        expect($virtualUser)
            ->active->toBeFalse()
            ->quota_bytes->toBe(777)
            ->username->toBe($citoyen->mail)
            ->and($virtualUser->userPassword)->toBe('{SSHA}secret');
    });
});

describe('change password', function (): void {
    it('stores the password as argon2id and clears the legacy hash', function (): void {
        $citoyen = Citoyen::factory()->create();
        $virtualUser = $citoyen->virtualUser;
        $virtualUser->update(['legacy_password' => '{SSHA}legacy', 'quota_bytes' => 777]);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->callAction('changePassword', [
                'password' => 'Correct-Horse-Battery-1',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $virtualUser->refresh();

        expect($virtualUser->userPassword)->toStartWith('{ARGON2ID}$argon2id$v=19$m=32768,t=4,p=1$')
            ->and(password_verify('Correct-Horse-Battery-1', mb_substr($virtualUser->userPassword, 10)))->toBeTrue()
            ->and($virtualUser->legacy_password)->toBeNull()
            ->and($virtualUser->quota_bytes)->toBe(777);
    });

    it('generates a password that passes validation', function (): void {
        $citoyen = Citoyen::factory()->create();
        $generated = null;

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->mountAction('changePassword')
            ->callAction(TestAction::make('generatePassword')->schemaComponent('password', schema: 'mountedActionSchema0'))
            ->assertActionDataSet(function (array $data) use (&$generated): bool {
                $generated = $data['password'];

                return filled($generated);
            })
            ->callMountedAction()
            ->assertHasNoActionErrors();

        expect(password_verify($generated, mb_substr($citoyen->virtualUser->refresh()->userPassword, 10)))->toBeTrue();
    });

    it('rejects an invalid password and keeps the current one', function (array $data): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['legacy_password' => '{SSHA}legacy']);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->callAction('changePassword', $data)
            ->assertHasActionErrors();

        $virtualUser = $citoyen->virtualUser->refresh();

        expect($virtualUser->userPassword)->toBeNull()
            ->and($virtualUser->legacy_password)->toBe('{SSHA}legacy');
    })->with([
        'shorter than 12 characters' => [['password' => 'Eleven-Char']],
        'no mixed case' => [['password' => 'lowercase-only-1']],
        'no number' => [['password' => 'No-Digits-In-Here']],
        'empty' => [['password' => '']],
    ]);
});

describe('hand', function (): void {
    it('keeps the legacy hash while an admin has the hand', function (): void {
        $citoyen = Citoyen::factory()->create();
        $virtualUser = $citoyen->virtualUser;
        $virtualUser->update(['userPassword' => null, 'legacy_password' => '{SSHA}original']);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->callAction('hand', ['password' => 'Temporary-Pass-1'])
            ->assertHasNoActionErrors()
            ->assertNotified();

        expect($citoyen->refresh()->hand_password)->toBe('{SSHA}original')
            ->and(password_verify('Temporary-Pass-1', mb_substr($virtualUser->refresh()->userPassword, 10)))->toBeTrue()
            ->and($virtualUser->legacy_password)->toBeNull();
    });

    it('gives back the original password', function (?string $userPassword, ?string $legacyPassword, string $effective): void {
        $citoyen = Citoyen::factory()->create();
        $virtualUser = $citoyen->virtualUser;
        $virtualUser->update(['userPassword' => $userPassword, 'legacy_password' => $legacyPassword]);

        $citoyen->takeOver('Temporary-Pass-1');

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->callAction('restoreHand')
            ->assertNotified();

        expect($citoyen->refresh()->hand_password)->toBeNull()
            ->and($virtualUser->refresh()->userPassword)->toBe($effective)
            ->and($virtualUser->legacy_password)->toBeNull();
    })->with([
        'legacy hash' => [null, '{SSHA}original', '{SSHA}original'],
        'argon2id hash' => ['{ARGON2ID}original', null, '{ARGON2ID}original'],
        'both set, as Dovecot prefers userPassword' => ['{ARGON2ID}original', '{SSHA}old', '{ARGON2ID}original'],
    ]);

    it('offers only the action matching the state', function (): void {
        $citoyen = Citoyen::factory()->create();

        $component = livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->assertActionVisible('hand')
            ->assertActionHidden('restoreHand')
            ->callAction('hand', ['password' => 'Temporary-Pass-1'])
            ->assertActionHidden('hand')
            ->assertActionVisible('restoreHand');

        $component->callAction('restoreHand')
            ->assertActionVisible('hand')
            ->assertActionHidden('restoreHand');
    });

    it('never overwrites the kept hash with the temporary password', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['userPassword' => null, 'legacy_password' => '{SSHA}original']);
        $citoyen->takeOver('Temporary-Pass-1');

        $citoyen->takeOver('Second-Temporary-2');

        expect($citoyen->refresh()->hand_password)->toBe('{SSHA}original')
            ->and(password_verify('Second-Temporary-2', mb_substr($citoyen->virtualUser->refresh()->userPassword, 10)))->toBeTrue();
    });

    it('rejects a temporary password outside the policy', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['userPassword' => null, 'legacy_password' => '{SSHA}original']);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->callAction('hand', ['password' => 'too-weak'])
            ->assertHasActionErrors(['password']);

        expect($citoyen->refresh()->hand_password)->toBeNull()
            ->and($citoyen->virtualUser->refresh()->legacy_password)->toBe('{SSHA}original');
    });

    it('never exposes the kept hash', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->virtualUser->update(['userPassword' => null, 'legacy_password' => '{SSHA}keptHashValue']);
        $citoyen->takeOver('Temporary-Pass-1');

        expect($citoyen->refresh()->toArray())->not->toHaveKey('hand_password');

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])->assertDontSee('keptHashValue');
        livewire(EditCitoyen::class, ['record' => $citoyen->id])->assertDontSee('keptHashValue');
    });
});

describe('create page', function (): void {
    beforeEach(function (): void {
        $this->validData = [
            'sn' => 'Martin',
            'givenName' => 'Alice',
            'mail' => 'Alice.Martin@marche.be',
            'employeeNumber' => '62011108917',
            'postalAddress' => 'Rue du Parc 1',
            'postalCode' => '6900',
            'l' => 'Marche-en-Famenne',
            'password' => 'Correct-Horse-Battery-1',
            'quota_bytes' => 500,
            'description' => 'Compte de test',
        ];
    });

    it('links to the create page from the list', function (): void {
        livewire(ListCitoyens::class)->assertActionExists('create');
    });

    it('creates the citoyen and its login', function (): void {
        livewire(CreateCitoyen::class)
            ->fillForm($this->validData)
            ->call('create')
            ->assertHasNoFormErrors();

        $citoyen = Citoyen::query()->where('mail', 'alice.martin@marche.be')->sole();

        expect($citoyen)
            ->uid->toBe('alice.martin')
            ->sn->toBe('Martin')
            ->givenName->toBe('Alice')
            ->employeeNumber->toBe('62011108917')
            ->postalAddress->toBe('Rue du Parc 1')
            ->homeDirectory->toBe('/var/spool/dovecot/mail/a/alice.martin')
            ->description->toBe('Compte de test')
            ->and(mb_strlen($citoyen->auth_token))->toBe(64);

        $virtualUser = $citoyen->virtualUser;

        expect($virtualUser)
            ->quota_bytes->toBe(500)
            ->active->toBeTrue()
            ->legacy_password->toBeNull()
            ->and($virtualUser->userPassword)->toStartWith('{ARGON2ID}$argon2id$')
            ->and(password_verify('Correct-Horse-Battery-1', mb_substr($virtualUser->userPassword, 10)))->toBeTrue();
    });

    it('shows the credentials to copy, then opens the citoyen', function (): void {
        $component = livewire(CreateCitoyen::class)
            ->fillForm($this->validData)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertActionMounted('credentials')
            ->assertNoRedirect()
            ->assertActionDataSet(function (array $data): bool {
                return str_contains($data['message'], 'alice.martin@marche.be')
                    && str_contains($data['message'], "d'utilisateur est : alice.martin")
                    && str_contains($data['message'], 'mot de passe est : Correct-Horse-Battery-1');
            })
            ->assertMountedActionModalSee('Copier');

        expect(serialize(session()->all()))->not->toContain('Correct-Horse-Battery-1');

        $citoyen = Citoyen::query()->where('mail', 'alice.martin@marche.be')->sole();

        $component
            ->callMountedAction()
            ->assertRedirect(CitoyenResource::getUrl('view', ['record' => $citoyen]));
    });

    it('prefills the postal code and the quota', function (): void {
        livewire(CreateCitoyen::class)->assertSchemaStateSet(['postalCode' => '6900', 'quota_bytes' => 350]);
    });

    it('rejects an address already used by a citoyen or a login', function (string $username): void {
        Citoyen::factory()->create(['mail' => 'taken@marche.be']);
        VirtualUser::factory()->create(['username' => 'login.only@marche.be']);
        $total = Citoyen::query()->count();

        livewire(CreateCitoyen::class)
            ->fillForm([...$this->validData, 'mail' => $username])
            ->call('create')
            ->assertHasFormErrors(['mail' => 'unique']);

        expect(Citoyen::query()->count())->toBe($total);
    })->with(['taken@marche.be', 'login.only@marche.be']);

    it('rejects an address it cannot store', function (string $mail): void {
        livewire(CreateCitoyen::class)
            ->fillForm([...$this->validData, 'mail' => $mail])
            ->call('create')
            ->assertHasFormErrors(['mail']);

        expect(Citoyen::query()->count())->toBe(0)
            ->and(VirtualUser::query()->count())->toBe(0);
    })->with([
        'other domain' => 'alice@example.be',
        'path traversal' => '../alice@marche.be',
        'too long for virtual_users' => str_repeat('a', 56).'@marche.be',
    ]);

    it('rejects a password outside the policy', function (): void {
        livewire(CreateCitoyen::class)
            ->fillForm([...$this->validData, 'password' => 'too-weak'])
            ->call('create')
            ->assertHasFormErrors(['password']);
    });

    it('requires the mandatory fields', function (string $field): void {
        livewire(CreateCitoyen::class)
            ->fillForm([...$this->validData, $field => null])
            ->call('create')
            ->assertHasFormErrors([$field => 'required']);
    })->with(['sn', 'mail', 'employeeNumber', 'postalAddress', 'postalCode', 'l', 'password', 'quota_bytes']);

    it('generates the address from the name', function (): void {
        livewire(CreateCitoyen::class)
            ->fillForm(['givenName' => 'Élodie', 'sn' => "D'Hoore"])
            ->callAction(TestAction::make('generateEmail')->schemaComponent('mail', schema: 'form'))
            ->assertSchemaStateSet(['mail' => 'elodie.dhoore@marche.be']);
    });

    it('generates a password', function (): void {
        livewire(CreateCitoyen::class)
            ->callAction(TestAction::make('generatePassword')->schemaComponent('password', schema: 'form'))
            ->assertSchemaStateSet(function (array $state): array {
                expect($state['password'])->toHaveLength(12);

                return [];
            });
    });
});

describe('sensitive data', function (): void {
    it('never renders the token hash but shows the national number', function (): void {
        $citoyen = Citoyen::factory()->create([
            'auth_token' => 'secretAuthTokenValue456',
            'employeeNumber' => '62011108917',
        ]);

        livewire(ViewCitoyen::class, ['record' => $citoyen->id])
            ->assertSee('62011108917')
            ->assertDontSee('secretAuthTokenValue456');
    });

    it('keeps the national number and recovery fields out of the table', function (): void {
        livewire(ListCitoyens::class)
            ->assertTableColumnDoesNotExist('employeeNumber')
            ->assertTableColumnDoesNotExist('recovery_email')
            ->assertTableColumnDoesNotExist('recovery_phone');
    });

    it('hides the hashes when serialized', function (): void {
        $citoyen = Citoyen::factory()->create();
        $citoyen->forceFill(['remember_token' => 'remember'])->save();

        expect($citoyen->refresh()->toArray())
            ->not->toHaveKeys(['auth_token', 'remember_token']);
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
        ]);

        livewire(EditCitoyen::class, ['record' => $citoyen->id])
            ->fillForm([
                'sn' => 'Nouveau',
                'mail' => 'hack@example.be',
                'uid' => 'hack',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $citoyen->refresh();

        expect($citoyen)
            ->sn->toBe('Nouveau')
            ->mail->toBe('alice.martin@marche.be')
            ->uid->toBe('alice.martin');
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

    it('forbids the create page to a user without the role', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        livewire(CreateCitoyen::class)->assertForbidden();
    });

    it('offers no delete action on the edit page', function (): void {
        livewire(EditCitoyen::class, ['record' => Citoyen::factory()->create()->id])
            ->assertActionDoesNotExist(DeleteAction::class);
    });

    it('denies delete even to a super administrator', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $citoyen = Citoyen::factory()->create();
        $policy = new CitoyenPolicy;

        expect($policy->create($admin))->toBeTrue()
            ->and($policy->delete($admin, $citoyen))->toBeFalse()
            ->and($policy->viewAny($admin))->toBeTrue()
            ->and($policy->update($admin, $citoyen))->toBeTrue();
    });
});
