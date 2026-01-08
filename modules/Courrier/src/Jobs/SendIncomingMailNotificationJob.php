<?php

namespace AcMarche\Courrier\Jobs;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Mail\IncomingMailNotification;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

final class SendIncomingMailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Carbon $mailDate,
    ) {
    }

    public function handle(): void
    {
        $recipients = Recipient::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($recipients as $recipient) {
            $incomingMails = $this->getIncomingMailsForRecipient($recipient);

            if ($incomingMails->isEmpty()) {
                continue;
            }

            $includeAttachments = $recipient->receives_attachments;

            Mail::to($recipient->email)
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

    /**
     * @return Collection<int, IncomingMail>
     */
    private function getIncomingMailsForRecipient(Recipient $recipient): Collection
    {
        $baseQuery = IncomingMail::query()
            ->where('is_notified', false)
            ->whereDate('mail_date', $this->mailDate)
            ->with(['services', 'recipients', 'attachments', 'category']);

        if ($this->recipientHasIndexRole($recipient)) {
            return $baseQuery->get();
        }

        return $baseQuery
            ->where(function ($query) use ($recipient): void {
                $query->whereHas('recipients', function ($q) use ($recipient): void {
                    $q->where('recipients.id', $recipient->id);
                })
                    ->orWhereHas('services', function ($q) use ($recipient): void {
                        $serviceIds = $recipient->services()->pluck('services.id');
                        $q->whereIn('services.id', $serviceIds);
                    });
            })
            ->get();
    }

    private function recipientHasIndexRole(Recipient $recipient): bool
    {
        if (!$recipient->username) {
            return false;
        }

        $user = User::query()
            ->where('username', $recipient->username)
            ->first();

        if (!$user) {
            return false;
        }

        return Gate::check('courrier-index', ['user' => $user]);
    }
}
