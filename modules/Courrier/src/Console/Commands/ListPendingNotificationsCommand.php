<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Mail\IncomingMailNotification;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Override;

/**
 * Lists every notification e-mail that {@see \AcMarche\Courrier\Jobs\SendIncomingMailNotificationJob}
 * would send for a given date, along with its subject and recipient.
 *
 * This is a read-only debugging aid: it replicates the job's selection logic
 * without dispatching mail or marking any incoming mail as notified.
 */
final class ListPendingNotificationsCommand extends Command
{
    #[Override]
    protected $signature = 'courrier:notifications:list
        {--date= : The mail date to inspect (Y-m-d), defaults to today}';

    #[Override]
    protected $description = 'List the pending incoming-mail notifications (subject and recipients) without sending them';

    public function handle(): int
    {
        $mailDate = $this->option('date') !== null
            ? Date::parse((string) $this->option('date'))
            : Date::now();

        $this->info(sprintf('Pending notifications for %s', $mailDate->format('Y-m-d')));
        $this->newLine();

        $repository = new IncomingMailRepository();
        $subject = (new IncomingMailNotification(new Recipient(), collect()))->envelope()->subject;

        $recipients = Recipient::query()
            ->whereNotNull('email')
            ->get();

        $rows = [];
        $totalMails = 0;

        foreach ($recipients as $recipient) {
            $incomingMails = $repository->getIncomingMailsForRecipient($recipient, $mailDate);

            if ($incomingMails->isEmpty()) {
                continue;
            }

            $totalMails += $incomingMails->count();

            $rows[] = [
                mb_trim("{$recipient->last_name} {$recipient->first_name}"),
                $recipient->email,
                $subject,
                $incomingMails->count(),
                $incomingMails->pluck('id')->implode(', '),
                $recipient->receives_attachments ? 'yes' : 'no',
            ];
        }

        if ($rows === []) {
            $this->warn('No notification would be sent for this date.');

            $this->reportRecipientsWithoutEmail();

            return self::SUCCESS;
        }

        $this->table(
            ['Recipient', 'Email', 'Subject', 'Mails', 'Mail IDs', 'Attachments'],
            $rows,
        );

        $this->info(sprintf(
            '%d recipient(s) would be notified about %d incoming mail(s).',
            count($rows),
            $totalMails,
        ));

        $this->reportRecipientsWithoutEmail();

        return self::SUCCESS;
    }

    private function reportRecipientsWithoutEmail(): void
    {
        $withoutEmail = Recipient::query()->whereNull('email')->count();

        if ($withoutEmail > 0) {
            $this->newLine();
            $this->warn(sprintf(
                '%d recipient(s) have no e-mail address and are skipped entirely.',
                $withoutEmail,
            ));
        }
    }
}
