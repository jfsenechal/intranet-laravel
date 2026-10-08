<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Jobs;

use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use AcMarche\MailingList\Models\Sender;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendEmailJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Email $email,
        public EmailRecipient $recipient,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $sender = $this->email->sender ?? $this->email->load('sender')->sender;

            $mailer = $sender instanceof Sender
                ? $sender->resolveMailer()
                : Mail::mailer(config('mail.default'));

            $mailer
                ->to($this->recipient->email_address)
                ->send(new NewsletterMail($this->email, $this->recipient));

            $this->recipient->update([
                'status' => RecipientStatus::Sent,
                'sent_at' => now(),
            ]);

            $this->email->increment('sent_count');
        } catch (Throwable $e) {
            $this->recipient->update([
                'status' => RecipientStatus::Failed,
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);

            throw $e;
        }
    }
}
