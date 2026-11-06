<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\SendCitoyenMessagePage;
use AcMarche\EmailManagement\Mail\CitoyenMessage;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);

    Mail::fake();
});

it('queues the message once for each active citoyen', function (): void {
    $alice = Citoyen::factory()->create(['mail' => 'alice@marche.be']);
    Citoyen::factory()->create(['mail' => 'bob@marche.be']);
    $inactive = Citoyen::factory()->create(['mail' => 'carol@marche.be']);
    $inactive->virtualUser->update(['active' => false]);

    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'subject' => 'Coupure du webmail',
            'body' => '<p>Le webmail sera indisponible lundi.</p>',
        ])
        ->call('send')
        ->assertHasNoFormErrors()
        ->assertNotified('Message mis en file d\'attente');

    Mail::assertQueuedCount(2);
    Mail::assertQueued(CitoyenMessage::class, fn (CitoyenMessage $mail): bool => $mail->hasTo($alice->mail)
        && $mail->subject === 'Coupure du webmail'
        && $mail->body === '<p>Le webmail sera indisponible lundi.</p>');
    Mail::assertQueued(CitoyenMessage::class, fn (CitoyenMessage $mail): bool => $mail->hasTo('bob@marche.be'));
});

it('spaces the mails by the configured delay', function (): void {
    config()->set('email-management.citoyen_message.seconds_between_mails', 2);
    $this->freezeTime();
    Citoyen::factory()->count(3)->create();

    // MailFake::later() drops the delay, so the real mailer has to reach a faked queue.
    Queue::fake();
    Mail::swap(new MailManager(app()));

    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'subject' => 'Coupure du webmail',
            'body' => '<p>Texte</p>',
        ])
        ->call('send')
        ->assertHasNoFormErrors();

    $delays = Queue::pushed(SendQueuedMailable::class)
        ->map(fn (SendQueuedMailable $job): int => (int) now()->diffInSeconds($job->delay))
        ->sort()
        ->values()
        ->all();

    expect($delays)->toBe([0, 2, 4]);
});

it('sends the message from the configured citoyen sender address', function (): void {
    config()->set('email-management.citoyen_message.from.address', 'info@marche.be');

    $mail = new CitoyenMessage('<p>Texte</p>', 'Objet');

    $mail->assertFrom('info@marche.be');
});

it('renders the message inside the Ville de Marche-en-Famenne template', function (): void {
    $mail = new CitoyenMessage('<p>Le webmail sera indisponible lundi.</p>', 'Objet');

    $mail->assertSeeInHtml('<p>Le webmail sera indisponible lundi.</p>', false);
    $mail->assertSeeInHtml('L\'équipe informatique de la Ville de Marche-en-Famenne.', false);
});

it('requires a subject and a body', function (): void {
    Citoyen::factory()->create();

    livewire(SendCitoyenMessagePage::class)
        ->fillForm([
            'subject' => '',
            'body' => '',
        ])
        ->call('send')
        ->assertHasFormErrors(['subject' => 'required', 'body']);

    Mail::assertNothingOutgoing();
});

it('forbids users without the email admin role', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    livewire(SendCitoyenMessagePage::class)->assertForbidden();
});
