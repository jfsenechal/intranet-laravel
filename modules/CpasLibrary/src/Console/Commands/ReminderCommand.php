<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Console\Commands;

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ViewFiche;
use AcMarche\CpasLibrary\Mail\ReminderMail;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ReminderCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'cpas-library:reminder';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Send the daily reminder digest for fiches whose reminder date is today';

    public function handle(): int
    {
        $recipients = RolesEnum::libraryUsers();

        if ($recipients->isEmpty()) {
            $this->warn('No user holds a library role (ROLE_LIBRARY or ROLE_LIBRARY_ADMIN).');

            return SfCommand::SUCCESS;
        }

        /** @var Collection<int, Fiche> $fiches */
        $fiches = Fiche::query()
            ->whereDate('date_rappel', Carbon::today())
            ->orderBy('createdAt')
            ->get();

        if ($fiches->isEmpty()) {
            $this->info('No reminders for today.');

            return SfCommand::SUCCESS;
        }

        $urls = $this->buildUrls($fiches);

        foreach ($recipients as $recipient) {
            Mail::to(new Address($recipient->email, $recipient->fullNameAsString()))
                ->send(new ReminderMail($fiches, $urls));
        }

        $this->info("Sent {$fiches->count()} reminder(s) to {$recipients->count()} library user(s).");

        return SfCommand::SUCCESS;
    }

    /**
     * Build a fiche view URL indexed by fiche id.
     *
     * @param  Collection<int, Fiche>  $fiches
     * @return array<int, string>
     */
    private function buildUrls(Collection $fiches): array
    {
        Filament::setCurrentPanel(Filament::getPanel('cpas-library-panel'));

        return $fiches
            ->mapWithKeys(fn (Fiche $fiche): array => [
                $fiche->id => ViewFiche::getUrl(['record' => $fiche]),
            ])
            ->all();
    }
}
