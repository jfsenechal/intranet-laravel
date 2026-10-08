<?php

declare(strict_types=1);

use AcMarche\MailingList\Handler\MailerHandler;
use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use AcMarche\MailingList\Models\Sender;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * Create an Email (and Sender) owned by the authenticated test user so the
 * OwnerScope-guarded relationships resolve when rendering the mailable.
 *
 * @param  array<string, mixed>  $attributes
 */
function emailForCurrentUser(array $attributes = []): Email
{
    $username = auth()->user()->username;
    $sender = Sender::factory()->create(['username' => $username]);

    return Email::factory()->create([
        'username' => $username,
        'sender_id' => $sender->id,
        ...$attributes,
    ]);
}

it('includes an unsubscribe link in the email when enabled', function () {
    $email = emailForCurrentUser(['unsubscribe_enabled' => true]);
    $recipient = EmailRecipient::factory()->for($email)->create();

    $rendered = (new NewsletterMail($email, $recipient))->render();

    expect($rendered)->toContain('Se désabonner');
});

it('omits the unsubscribe link when disabled', function () {
    $email = emailForCurrentUser(['unsubscribe_enabled' => false]);
    $recipient = EmailRecipient::factory()->for($email)->create();

    $rendered = (new NewsletterMail($email, $recipient))->render();

    expect($rendered)->not->toContain('Se désabonner');
});

it('shows the confirmation page from a valid signed link', function () {
    $recipient = EmailRecipient::factory()->create();

    get($recipient->unsubscribeUrl())
        ->assertOk()
        ->assertSee('Se désabonner')
        ->assertSee($recipient->email_address);
});

it('rejects an unsigned unsubscribe link', function () {
    $recipient = EmailRecipient::factory()->create();

    get(route('unsubscribe.show', ['recipient' => $recipient->id]))
        ->assertForbidden();
});

it('unsubscribes the recipient and its contact when confirmed', function () {
    $contact = Contact::factory()->create();
    $recipient = EmailRecipient::factory()->for($contact)->create();

    $confirmUrl = URL::signedRoute('unsubscribe.store', ['recipient' => $recipient->id]);

    post($confirmUrl)
        ->assertOk()
        ->assertSee('Désabonnement confirmé');

    expect($recipient->refresh()->isUnsubscribed())->toBeTrue();

    assertDatabaseHas('contacts', ['id' => $contact->id]);
    expect($contact->fresh()->isUnsubscribed())->toBeTrue();
});

it('skips unsubscribed contacts when syncing recipients', function () {
    $username = auth()->user()->username;

    $subscribed = Contact::factory()->create(['username' => $username]);
    $unsubscribed = Contact::factory()->create([
        'username' => $username,
        'unsubscribed_at' => now(),
    ]);

    $addressBook = AddressBook::factory()->create(['username' => $username]);
    $addressBook->contacts()->attach([$subscribed->id, $unsubscribed->id]);

    $email = Email::factory()->create(['username' => $username]);

    MailerHandler::syncRecipients($email, [$addressBook->id], []);

    expect($email->recipients()->count())->toBe(1);
    expect($email->recipients()->first()->email_address)->toBe($subscribed->email);
});
