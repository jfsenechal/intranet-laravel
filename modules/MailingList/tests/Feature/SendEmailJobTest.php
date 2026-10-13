<?php

declare(strict_types=1);

use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Jobs\SendEmailJob;
use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use AcMarche\MailingList\Models\Sender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

it('sends from a queue worker that has no authenticated user', function () {
    Mail::fake();

    $username = auth()->user()->username;
    $sender = Sender::factory()->create(['username' => $username]);
    $email = Email::factory()->create(['username' => $username, 'sender_id' => $sender->id]);
    $recipient = EmailRecipient::factory()->for($email)->create();

    $payload = serialize(new SendEmailJob($email->load('sender'), $recipient));

    Auth::forgetGuards();

    $job = unserialize($payload);
    $job->handle();

    Mail::assertSent(NewsletterMail::class, fn (NewsletterMail $mail): bool => $mail->hasTo($recipient->email_address));
    expect($recipient->fresh()->status)->toBe(RecipientStatus::Sent);
});
