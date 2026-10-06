<?php

declare(strict_types=1);

use AcMarche\Agent\Enums\RolesEnum;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ViewProfile;
use AcMarche\Agent\Mail\ProfileChangesMail;
use AcMarche\Agent\Mail\ShareProfileMail;
use AcMarche\Agent\Mail\WelcomeMail;
use AcMarche\Agent\Models\Profile;
use AcMarche\Agent\Models\Share;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Ldap\UserLdap;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('agent-panel'));
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT_ADMIN->value]);
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->adminUser->roles()->attach($adminRole);
    $this->actingAs($this->adminUser);
    config()->set('agent.informatique_email', 'informatique@marche.be');
});

describe('emails', function (): void {
    beforeEach(function (): void {
        DirectoryEmulator::setup('default');
    });

    afterEach(function (): void {
        DirectoryEmulator::tearDown();
    });

    it('displays the ldap email of the account linked by username', function (): void {
        $userLdap = new UserLdap;
        $userLdap->cn = 'Alice Martin';
        $userLdap->samaccountname = 'amartin';
        $userLdap->mail = 'alice.martin@marche.be';
        $userLdap->save();

        $profile = Profile::factory()->create(['username' => 'amartin']);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('alice.martin@marche.be');
    });

    it('shows a placeholder when no ldap account matches the username', function (): void {
        $profile = Profile::factory()->create(['username' => 'unknown']);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('Aucune adresse dans la LDAP');
    });

    it('displays the shared mailboxes stored on the profile', function (): void {
        $profile = Profile::factory()->create([
            'username' => 'amartin',
            'emails' => ['urbanisme@marche.be', 'travaux@marche.be'],
        ]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('urbanisme@marche.be')
            ->assertSee('travaux@marche.be');
    });
});

describe('employee dates', function (): void {
    it('displays the hire and leave dates of the linked hrm employee', function (): void {
        $employee = Employee::factory()->create([
            'hired_at' => '2015-03-01',
            'left_at' => '2026-09-30',
        ]);
        $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('01/03/2015')
            ->assertSee('30/09/2026');
    });
});

describe('active contracts', function (): void {
    it('lists only the active contracts of the linked hrm employee', function (): void {
        $employee = Employee::factory()->create();
        Contract::factory()->for($employee)->create([
            'job_title' => 'Agent administratif',
            'is_closed' => false,
            'is_suspended' => false,
        ]);
        Contract::factory()->for($employee)->create([
            'job_title' => 'Ouvrier communal',
            'is_closed' => true,
        ]);
        $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('Contrats actifs')
            ->assertSee('Agent administratif')
            ->assertDontSee('Ouvrier communal');
    });

    it('hides the active contracts without a linked hrm employee', function (): void {
        $profile = Profile::factory()->create(['employee_id' => null]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertDontSee('Contrats actifs');
    });
});

describe('photo', function (): void {
    it('shows the directory photo of the linked hrm employee', function (): void {
        $employee = Employee::factory()->create([
            'show_photo' => true,
            'photo' => 'employees/alice.jpg',
        ]);
        $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('employees/alice.jpg');
    });

    it('falls back to the avatar of the user sharing the username', function (): void {
        User::factory()->create(['username' => 'amartin', 'avatar_url' => 'avatars/amartin.png']);
        $profile = Profile::factory()->create(['username' => 'amartin', 'employee_id' => null]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('avatars/amartin.png');
    });

    it('falls back to a generated avatar without employee nor user', function (): void {
        $profile = Profile::factory()->create([
            'username' => 'nobody',
            'employee_id' => null,
            'first_name' => 'Alice',
            'last_name' => 'Martin',
        ]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('ui-avatars.com/api/?size=160&amp;name=Alice+Martin', false);
    });
});

describe('supervisors', function (): void {
    it('displays the names of the supervising employees instead of their ids', function (): void {
        $supervisor = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $profile = Profile::factory()->create(['supervisors' => [(string) $supervisor->getKey()]]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('Dupont Marie');
    });
});

describe('export resume action', function (): void {
    it('renders the export resume action on the view page', function (): void {
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionExists('exportResume');
    });

    it('generates the resume pdf from the agent resume view', function (): void {
        Pdf::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('exportResume')
            ->assertHasNoActionErrors();

        Pdf::assertRespondedWithPdf(
            fn (Spatie\LaravelPdf\PdfBuilder $pdf): bool => $pdf->viewName === 'agent::pdf.resume'
                && ($pdf->viewData['profile'] ?? null)?->is($profile) === true
                && str_contains($pdf->getHtml(), $profile->fullName())
        );
    });
});

describe('resume pdf view', function (): void {
    it('lists the supervisors by name instead of employee id', function (): void {
        $supervisor = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $profile = Profile::factory()->create(['supervisors' => [(string) $supervisor->getKey(), null]]);

        Pdf::fake();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('exportResume');

        Pdf::assertRespondedWithPdf(
            fn (Spatie\LaravelPdf\PdfBuilder $pdf): bool => str_contains($pdf->getHtml(), 'Dupont Marie')
        );
    });
});

describe('welcome pdf view', function (): void {
    it('renders the welcome letter with the login details', function (): void {
        $profile = Profile::factory()->create();

        $html = view('agent::pdf.welcome', [
            'profile' => $profile,
            'employee' => null,
            'email' => 'agent@marche.be',
            'modules' => collect(),
            'folderPaths' => ['Data / Urbanisme'],
            'password' => 'Sup3rSecret!Pass',
            'notes' => 'Arrivée le 1er septembre',
        ])->render();

        expect($html)
            ->toContain($profile->username)
            ->toContain('Sup3rSecret!Pass')
            ->toContain('agent@marche.be')
            ->toContain('Data / Urbanisme')
            ->toContain('Arrivée le 1er septembre');
    });
});

describe('welcome mail action', function (): void {
    it('renders the welcome mail action for an agent administrator', function (): void {
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionExists('sendWelcomeMail');
    });

    it('hides the welcome mail action from a non administrator', function (): void {
        $agentRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach($agentRole);
        $this->actingAs($user);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionHidden('sendWelcomeMail');
    });

    it('sends the welcome mail with the notes and copies the it department', function (): void {
        Mail::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('sendWelcomeMail', data: [
                'password' => 'Sup3rSecret!Pass',
                'notes' => 'Arrivée le 1er septembre',
            ])
            ->assertHasNoActionErrors();

        Mail::assertQueued(
            WelcomeMail::class,
            fn (WelcomeMail $mail): bool => $mail->profile->is($profile)
                && $mail->password === 'Sup3rSecret!Pass'
                && $mail->notes === 'Arrivée le 1er septembre'
                && $mail->hasCc('informatique@marche.be')
        );
    });

    it('rejects a password shorter than twelve characters', function (): void {
        Mail::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('sendWelcomeMail', data: [
                'password' => 'short',
                'notes' => null,
            ])
            ->assertHasActionErrors(['password']);

        Mail::assertNothingQueued();
    });
});

describe('share profile action', function (): void {
    it('renders the share action for an agent administrator', function (): void {
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionExists('shareProfile');
    });

    it('hides the share action from a non administrator', function (): void {
        $agentRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach($agentRole);
        $this->actingAs($user);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionHidden('shareProfile');
    });

    it('grants the agent role to a recipient who does not have it yet', function (): void {
        Mail::fake();
        Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        $outsider = User::factory()->create(['email' => 'outsider@marche.be']);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('shareProfile', data: [
                'email' => 'outsider@marche.be',
                'notes' => null,
            ])
            ->assertHasNoActionErrors();

        expect($outsider->refresh()->hasRole(RolesEnum::ROLE_AGENT->value))->toBeTrue();

        Mail::assertQueued(ShareProfileMail::class);
    });

    it('leaves the roles of a recipient who already is an agent untouched', function (): void {
        Mail::fake();
        $agentRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        $agent = User::factory()->create(['email' => 'agent@marche.be']);
        $agent->roles()->attach($agentRole);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('shareProfile', data: [
                'email' => 'agent@marche.be',
                'notes' => null,
            ])
            ->assertHasNoActionErrors();

        expect($agent->refresh()->roles()->count())->toBe(1);
    });

    it('records the share and mails the recipient with a copy to the sender', function (): void {
        Mail::fake();
        Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        User::factory()->create(['email' => 'agent@marche.be']);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('shareProfile', data: [
                'email' => 'agent@marche.be',
                'notes' => 'Merci de compléter la partie matériel',
            ])
            ->assertHasNoActionErrors();

        expect(Share::query()->where('profile_id', $profile->getKey())->where('shared_for', 'agent@marche.be')->exists())
            ->toBeTrue();

        Mail::assertQueued(
            ShareProfileMail::class,
            fn (ShareProfileMail $mail): bool => $mail->profile->is($profile)
                && $mail->notes === 'Merci de compléter la partie matériel'
                && $mail->hasTo('agent@marche.be')
                && $mail->hasCc($this->adminUser->email)
        );
    });

    it('does not duplicate an existing share', function (): void {
        Mail::fake();
        Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        User::factory()->create(['email' => 'agent@marche.be']);

        $profile = Profile::factory()->create();
        $profile->shares()->save(new Share(['shared_by' => 'someone', 'shared_for' => 'agent@marche.be']));

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('shareProfile', data: [
                'email' => 'agent@marche.be',
                'notes' => null,
            ])
            ->assertHasNoActionErrors();

        expect(Share::query()->where('profile_id', $profile->getKey())->count())->toBe(1);

        Mail::assertQueued(ShareProfileMail::class);
    });

    it('requires a recipient', function (): void {
        Mail::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('shareProfile', data: [
                'email' => null,
                'notes' => null,
            ])
            ->assertHasActionErrors(['email']);

        Mail::assertNothingQueued();
    });
});

describe('send profile changes action', function (): void {
    it('renders the send changes action for an agent administrator', function (): void {
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionExists('sendProfileChanges');
    });

    it('hides the send changes action from an agent the profile was not delegated to', function (): void {
        $agentRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach($agentRole);
        $this->actingAs($user);

        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertActionHidden('sendProfileChanges');
    });

    it('mails the changes to the it department with a copy to the sender', function (): void {
        Mail::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('sendProfileChanges', data: [
                'notes' => 'Nouveau bureau et nouveau numéro de téléphone',
            ])
            ->assertHasNoActionErrors();

        Mail::assertQueued(
            ProfileChangesMail::class,
            fn (ProfileChangesMail $mail): bool => $mail->profile->is($profile)
                && $mail->notes === 'Nouveau bureau et nouveau numéro de téléphone'
                && $mail->hasTo('informatique@marche.be')
                && $mail->hasCc($this->adminUser->email)
        );
    });

    it('requires the changes to be described', function (): void {
        Mail::fake();
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('sendProfileChanges', data: [
                'notes' => null,
            ])
            ->assertHasActionErrors(['notes']);

        Mail::assertNothingQueued();
    });

    it('sends nothing when the it address is not configured', function (): void {
        Mail::fake();
        config()->set('agent.informatique_email', null);
        $profile = Profile::factory()->create();

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->callAction('sendProfileChanges', data: [
                'notes' => 'Nouveau bureau',
            ])
            ->assertHasNoActionErrors();

        Mail::assertNothingQueued();
    });
});
