<?php

declare(strict_types=1);

use AcMarche\Agent\Enums\RolesEnum;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ViewProfile;
use AcMarche\Agent\Mail\WelcomeMail;
use AcMarche\Agent\Models\Profile;
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
        $userLdap->cn = 'Ana Aguirre';
        $userLdap->samaccountname = 'aaguirre';
        $userLdap->mail = 'ana.aguirre@marche.be';
        $userLdap->save();

        $profile = Profile::factory()->create(['username' => 'aaguirre']);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('ana.aguirre@marche.be');
    });

    it('shows a placeholder when no ldap account matches the username', function (): void {
        $profile = Profile::factory()->create(['username' => 'unknown']);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('Aucune adresse dans la LDAP');
    });

    it('displays the shared mailboxes stored on the profile', function (): void {
        $profile = Profile::factory()->create([
            'username' => 'aaguirre',
            'emails' => ['urbanisme@marche.be', 'travaux@marche.be'],
        ]);

        Livewire::test(ViewProfile::class, ['record' => $profile->getKey()])
            ->assertSee('urbanisme@marche.be')
            ->assertSee('travaux@marche.be');
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
