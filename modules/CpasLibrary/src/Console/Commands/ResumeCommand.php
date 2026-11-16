<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Console\Commands;

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ViewFiche;
use AcMarche\CpasLibrary\Mail\ResumeMail;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ResumeCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'cpas-library:resume';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Send the weekly digest of fiches added over the last seven days';

    public function handle(): int
    {
        $recipients = RolesEnum::libraryUsers();

        if ($recipients->isEmpty()) {
            $this->warn('No user holds a library role (ROLE_LIBRARY or ROLE_LIBRARY_ADMIN).');

            return SfCommand::SUCCESS;
        }

        /** @var Collection<int, Fiche> $fiches */
        $fiches = Fiche::query()
            ->whereBetween('createdAt', [Carbon::today()->subDays(7), Carbon::now()])
            ->orderBy('createdAt')
            ->get();

        if ($fiches->isEmpty()) {
            $this->info('No fiches added over the last seven days.');

            return SfCommand::SUCCESS;
        }

        $urls = $this->buildUrls($fiches);

        foreach ($recipients as $recipient) {
            Mail::to(new Address($recipient->email, $recipient->fullNameAsString()))
                ->send(new ResumeMail($fiches, $urls));
        }

        $this->info("Sent a weekly digest of {$fiches->count()} fiche(s) to {$recipients->count()} library user(s).");

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
