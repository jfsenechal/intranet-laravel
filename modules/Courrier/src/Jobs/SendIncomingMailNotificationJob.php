<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Jobs;

use AcMarche\Courrier\Mail\IncomingMailNotification;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendIncomingMailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly CarbonInterface $mailDate,
        public readonly bool $force = false,
    ) {
    }

    public function handle(): void
    {
        if ($this->force) {
            IncomingMail::query()
                ->whereDate('mail_date', $this->mailDate)
                ->update(['is_notified' => false]);
        }

        $repository = new IncomingMailRepository();

        $recipients = Recipient::query()
            ->whereNotNull('email')
            ->get();

        foreach ($recipients as $recipient) {
            $incomingMails = $repository->getIncomingMailsForRecipient($recipient, $this->mailDate);

            if ($incomingMails->isEmpty()) {
                continue;
            }

            $includeAttachments = $recipient->receives_attachments;

            // Mail::to(new Address($recipient->email))
            Mail::to(new Address('jf@marche.be', $recipient->email))
                ->queue(
                    new IncomingMailNotification(
                        $recipient,
                        $incomingMails,
                        $includeAttachments,
                    )
                );

            $incomingMails->each(function (IncomingMail $mail): void {
                $mail->update(['is_notified' => true]);
            });
        }
    }
}
