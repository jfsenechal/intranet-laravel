<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\App\Sms\InforiusClient;
use AcMarche\Hrm\Console\Commands\Concerns\SendsDepartmentReminders;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ViewSmsReminder;
use AcMarche\Hrm\Models\SmsReminder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command as SfCommand;
use Throwable;

final class SmsReminderCommand extends Command
{
    use SendsDepartmentReminders;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:sms-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the daily SMS reminders, all departments together';

    public function handle(): int
    {
        $this->useHrmPanel();

        $this->sendSmsReminders(Carbon::today(), $this->failureRecipients());

        return SfCommand::SUCCESS;
    }

    /**
     * Unlike the mail reminders, SMS is not split per department: a single run
     * sends everything that is due, and failures are reported to every HR
     * mailbox configured through the HRM_REMINDERS_* variables.
     *
     * @return list<string>
     */
    private function failureRecipients(): array
    {
        $recipients = array_filter(array_map(
            'mb_trim',
            Arr::flatten((array) config('hrm.reminders.recipients', [])),
        ));

        return array_values(array_unique($recipients));
    }

    /**
     * @param  list<string>  $recipients
     */
    private function sendSmsReminders(Carbon $today, array $recipients): void
    {
        SmsReminder::query()
            ->where(function (Builder $query) use ($today): void {
                $query->whereDate('reminder_date', $today)
                    ->orWhereDate('other_reminder_date', $today);
            })
            // A reminder already sent today must not go out a second time when
            // the command is run again.
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('sent_at')
                    ->orWhereDate('sent_at', '!=', $today);
            })
            ->with('employee')
            ->get()
            ->each(fn (SmsReminder $sms) => $this->sendSmsReminder($sms, $recipients));
    }

    /**
     * Send one reminder through the SMS gateway. The outcome is always recorded
     * on the reminder; recipients are only mailed when the send fails, so the
     * failure does not go unnoticed.
     *
     * @param  list<string>  $recipients
     */
    private function sendSmsReminder(SmsReminder $sms, array $recipients): void
    {
        $number = (string) $sms->phone_number;
        $message = mb_trim(strip_tags((string) $sms->message));

        if ($number === '' || $message === '') {
            $this->recordSmsFailure($sms, $recipients, 'Numéro et message obligatoires');

            return;
        }

        try {
            $response = app(InforiusClient::class)->sendSms(
                number: $number,
                message: $message,
                customerReference: 'reminder-'.$number,
            );
        } catch (Throwable $exception) {
            $this->recordSmsFailure($sms, $recipients, $exception->getMessage());

            return;
        }

        if (! $response->isSuccessful()) {
            $this->recordSmsFailure(
                $sms,
                $recipients,
                $response->error ?? $response->messages[0]->errorMessage ?? 'Erreur inconnue',
            );

            return;
        }

        $sms->forceFill([
            'sent_at' => Carbon::now(),
            'result' => 'OK',
        ])->save();
    }

    /**
     * @param  list<string>  $recipients
     */
    private function recordSmsFailure(SmsReminder $sms, array $recipients, string $result): void
    {
        $sms->forceFill(['result' => $result])->save();

        $this->error(sprintf('SMS reminder #%s failed: %s', $sms->getKey(), $result));

        if ($recipients === []) {
            return;
        }

        $this->dispatchMail(
            $recipients,
            'SMS',
            $sms,
            ViewSmsReminder::getUrl(['record' => $sms]),
            $sms->employee,
        );
    }
}
