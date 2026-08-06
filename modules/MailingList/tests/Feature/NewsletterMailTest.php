<?php

declare(strict_types=1);

use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\Sender;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->sender = Sender::factory()->create(['username' => $this->user->username]);
});

it('renders the newsletter view', function (): void {
    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
        'subject' => 'Test Newsletter',
        'body' => '<p>Hello World</p>',
    ]);

    $mailable = new NewsletterMail($email, 'Jean Dupont');

    $mailable->assertHasSubject('Test Newsletter');
    $mailable->assertSeeInHtml('Hello World', false);
});
