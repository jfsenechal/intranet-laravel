<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\SendCitoyenMessagePage;
use AcMarche\EmailManagement\Mail\CitoyenMessage;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);

    Mail::fake();
});

it('queues the message once for each distinct recipient', function (): void {
    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'recipients' => ['alice@marche.be', 'bob@marche.be', 'alice@marche.be'],
            'subject' => 'Coupure du webmail',
            'body' => '<p>Le webmail sera indisponible lundi.</p>',
        ])
        ->call('send')
        ->assertHasNoFormErrors()
        ->assertNotified('Message mis en file d\'attente');

    Mail::assertQueuedCount(2);
    Mail::assertQueued(CitoyenMessage::class, fn (CitoyenMessage $mail): bool => $mail->hasTo('alice@marche.be')
        && $mail->subject === 'Coupure du webmail'
        && $mail->body === '<p>Le webmail sera indisponible lundi.</p>');
    Mail::assertQueued(CitoyenMessage::class, fn (CitoyenMessage $mail): bool => $mail->hasTo('bob@marche.be'));
});

it('sends the message from the configured citoyen sender address', function (): void {
    config()->set('email-management.citoyen_message.from.address', 'info@marche.be');

    $mail = new CitoyenMessage('<p>Texte</p>', 'Objet');

    $mail->assertFrom('info@marche.be');
});

it('rejects an invalid recipient address and queues nothing', function (): void {
    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'recipients' => ['not-an-email'],
            'subject' => 'Coupure du webmail',
            'body' => '<p>Texte</p>',
        ])
        ->call('send')
        ->assertHasFormErrors(['recipients.0' => 'email']);

    Mail::assertNothingOutgoing();
});

it('requires recipients, a subject and a body', function (): void {
    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'recipients' => [],
            'subject' => '',
            'body' => '',
        ])
        ->call('send')
        ->assertHasFormErrors(['recipients' => 'required', 'subject' => 'required', 'body']);

    Mail::assertNothingOutgoing();
});

it('forbids users without the email admin role', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    livewire(SendCitoyenMessagePage::class)->assertForbidden();
});
