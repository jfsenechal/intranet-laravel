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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendIncomingMailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public readonly CarbonInterface $mailDate,
        public readonly bool $force = false,
    ) {}

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

        // Mail is marked as notified only after the whole loop completes, using
        // the ids of successfully delivered mail. Marking inside the loop would
        // flip is_notified before later recipients are evaluated and starve every
        // recipient of the same mail after the first one (they query is_notified
        // = false and find nothing).
        $notifiedMailIds = [];

        foreach ($recipients as $recipient) {
            $incomingMails = $repository->getIncomingMailsForRecipient($recipient, $this->mailDate);

            if ($incomingMails->isEmpty()) {
                continue;
            }

            try {
                Mail::to(new Address($recipient->email))
                    ->send(
                        new IncomingMailNotification(
                            $recipient,
                            $incomingMails,
                            $recipient->receives_attachments,
                        )
                    );
            } catch (Throwable $throwable) {
                Log::error(sprintf(
                    'Courrier notification failed for %s: %s',
                    $recipient->email,
                    $throwable->getMessage(),
                ));

                continue;
            }

            $notifiedMailIds = [...$notifiedMailIds, ...$incomingMails->pluck('id')->all()];
        }

        if ($notifiedMailIds !== []) {
            IncomingMail::query()
                ->whereIn('id', array_unique($notifiedMailIds))
                ->update(['is_notified' => true]);
        }
    }
}
