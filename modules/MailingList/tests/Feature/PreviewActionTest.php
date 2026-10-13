<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Emails\Pages\ViewEmail;
use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use AcMarche\MailingList\Models\Sender;
use AcMarche\MailingList\Models\Signature;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('sends a preview email to the given address', function (): void {
    Mail::fake();

    $sender = Sender::factory()->create(['username' => auth()->user()->username]);
    $email = Email::factory()->create([
        'username' => auth()->user()->username,
        'sender_id' => $sender->id,
    ]);

    livewire(ViewEmail::class, ['record' => $email->id])
        ->callAction(TestAction::make('preview'), [
            'email' => 'preview@example.com',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail): bool {
        return $mail->hasTo('preview@example.com')
            && $mail->recipient->name === 'Apercu'
            && ! $mail->recipient->exists;
    });
});

it('renders the preview without an unsubscribe link for a transient recipient', function (): void {
    $sender = Sender::factory()->create(['username' => auth()->user()->username]);
    $email = Email::factory()->create([
        'username' => auth()->user()->username,
        'sender_id' => $sender->id,
        'unsubscribe_enabled' => true,
    ]);
    $email->load('sender');

    $recipient = new EmailRecipient([
        'email_address' => 'preview@example.com',
        'name' => 'Apercu',
    ]);

    $mailable = new NewsletterMail($email, $recipient);

    expect($mailable->content()->with['unsubscribeUrl'])->toBeNull();
    expect($mailable->headers()->text)->toBe([]);
});

it('appends the sender signature to the newsletter only when one is selected', function (): void {
    $signature = Signature::create([
        'username' => auth()->user()->username,
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'catherine.boldo@cpas.marche.be',
    ]);
    $sender = Sender::factory()->create(['username' => auth()->user()->username]);
    $email = Email::factory()->create([
        'username' => auth()->user()->username,
        'sender_id' => $sender->id,
    ]);
    $recipient = new EmailRecipient(['email_address' => 'preview@example.com', 'name' => 'Apercu']);

    expect((new NewsletterMail($email->load('sender'), $recipient))->render())
        ->not->toContain('catherine.boldo@cpas.marche.be');

    $sender->update(['signature_id' => $signature->id]);

    expect((new NewsletterMail($email->fresh()->load('sender'), $recipient))->render())
        ->toContain('catherine.boldo@cpas.marche.be');
});
