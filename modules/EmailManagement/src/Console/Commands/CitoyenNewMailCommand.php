<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Console\Commands;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Service\CitoyenMailbox;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;
use Throwable;

/**
 * Lists the citizens with unread mail in Maildir/new and for how long it has been waiting.
 *
 * It reads the mail store, so it has to run on a host that can read it. A mailbox that cannot
 * be read looks exactly like one without a Sieve script, which is why the command stops when
 * the mail root is unreadable and why --delete always asks before removing an account.
 */
final class CitoyenNewMailCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'email-management:citoyen-new-mail
                            {keyword? : mail address to filter on, every account when omitted}
                            {--only-with-mail : only list accounts with at least one unread message}
                            {--min-days= : only list accounts whose oldest unread message exceeds this number of days}
                            {--delete : offer to delete the listed accounts from the database, requires --min-days}';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'List the citizens with unread mail and for how long it has been waiting';

    public function handle(): int
    {
        $keyword = $this->argument('keyword');
        $minDays = $this->option('min-days') !== null ? (int) $this->option('min-days') : null;
        $mailRoot = (string) config('email-management.mail_root');

        if ($this->option('delete') && ($minDays === null || $minDays < 1)) {
            $this->components->error('--delete demande --min-days avec au moins 1 jour.');

            return SfCommand::FAILURE;
        }

        if (! File::isDirectory($mailRoot) || ! File::isReadable($mailRoot)) {
            $this->components->error("Le dossier {$mailRoot} n'est pas lisible : cette commande doit tourner sur le serveur de mail.");

            return SfCommand::FAILURE;
        }

        $citoyens = Citoyen::query()
            ->when($keyword, fn (Builder $query, string $keyword): Builder => $query->where('mail', 'like', "%{$keyword}%"))
            ->get();

        if ($citoyens->isEmpty()) {
            $this->components->error('Aucun compte trouvé'.($keyword ? ' pour '.$keyword : ''));

            return SfCommand::FAILURE;
        }

        $rows = $this->rows($citoyens->all(), $minDays, (bool) $this->option('only-with-mail') || $minDays !== null);

        if ($rows === []) {
            $this->line('Aucun compte correspondant.');

            return SfCommand::SUCCESS;
        }

        $this->table(
            ['mail', 'non lus', 'plus ancien', 'en attente depuis'],
            array_column($rows, 'cells')
        );
        $this->components->info(count($rows).' compte(s), '.array_sum(array_column($rows, 'count')).' message(s) non lu(s).');

        if ($this->option('delete')) {
            $this->deleteCitoyens(array_column($rows, 'citoyen'));
        }

        return SfCommand::SUCCESS;
    }

    /**
     * @param  array<int, Citoyen>  $citoyens
     * @return array<int, array{days: ?int, count: int, citoyen: Citoyen, cells: array<int, int|string>}>
     */
    private function rows(array $citoyens, ?int $minDays, bool $onlyWithMail): array
    {
        $now = CarbonImmutable::now();
        $rows = [];

        foreach ($citoyens as $citoyen) {
            $mailbox = new CitoyenMailbox(Citoyen::homeDirectoryFor($citoyen->mail));
            $count = $mailbox->countNewMails();

            if ($onlyWithMail && ! $count) {
                continue;
            }

            $oldestAt = $mailbox->oldestNewMailAt();
            $days = $oldestAt instanceof CarbonImmutable ? (int) $oldestAt->diffInDays($now) : null;

            if ($minDays !== null && ($days === null || $days < $minDays)) {
                continue;
            }

            $rows[] = [
                'days' => $days,
                'count' => $count ?? 0,
                'citoyen' => $citoyen,
                'cells' => [
                    $citoyen->mail,
                    $count ?? 'pas de Maildir/new',
                    $oldestAt?->format('d/m/Y') ?? '—',
                    $days !== null ? $days.' j' : '—',
                ],
            ];
        }

        usort($rows, fn (array $a, array $b): int => ($b['days'] ?? -1) <=> ($a['days'] ?? -1));

        return $rows;
    }

    /**
     * An account forwarding its mail is skipped: unread messages do not mean it is abandoned.
     * The mailbox directory is left on disk, the path to remove is reported instead.
     *
     * @param  array<int, Citoyen>  $citoyens
     */
    private function deleteCitoyens(array $citoyens): void
    {
        /** @var array<int, array{0: string, 1: string, 2: string}> $results */
        $results = [];

        foreach ($citoyens as $citoyen) {
            $results[] = [$citoyen->mail, ...$this->deleteCitoyen($citoyen)];
        }

        $this->newLine();
        $this->table(['mail', 'statut', 'détail'], $results);

        $statuses = array_count_values(array_column($results, 1));

        $this->components->info(sprintf(
            '%d supprimé(s), %d conservé(s), %d ignoré(s) pour cause de redirection, %d en erreur.',
            $statuses['Supprimé'] ?? 0,
            $statuses['Conservé'] ?? 0,
            $statuses['Ignoré'] ?? 0,
            $statuses['Erreur'] ?? 0,
        ));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function deleteCitoyen(Citoyen $citoyen): array
    {
        $homeDirectory = Citoyen::homeDirectoryFor($citoyen->mail);
        $mailbox = new CitoyenMailbox($homeDirectory);

        $this->newLine();
        $this->line($citoyen->mail);

        $redirects = $mailbox->sieveRedirects();

        if ($redirects !== []) {
            $this->components->warn('Redirection active vers : '.implode(', ', $redirects));

            return ['Ignoré', 'Redirection vers '.implode(', ', $redirects)];
        }

        if (! $this->confirm("Supprimer le compte {$citoyen->mail} ?")) {
            return ['Conservé', 'Suppression refusée'];
        }

        try {
            $citoyen->deleteWithLogin();
        } catch (Throwable $exception) {
            $this->components->error("Erreur lors de la suppression de {$citoyen->mail} : {$exception->getMessage()}");

            return ['Erreur', $exception->getMessage()];
        }

        return ['Supprimé', 'rm -rI '.$homeDirectory];
    }
}
