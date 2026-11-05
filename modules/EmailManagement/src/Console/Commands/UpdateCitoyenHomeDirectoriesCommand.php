<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Console\Commands;

use AcMarche\EmailManagement\Models\Citoyen;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

/**
 * Rewrites citoyens.homeDirectory to the path Dovecot computes for the mailbox.
 *
 * Only the column changes: no directory is moved on the mail server. Safe to run again, rows
 * already on the new path are skipped.
 */
final class UpdateCitoyenHomeDirectoriesCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'email-management:update-citoyen-home-directories
                            {--dry-run : list the changes without saving them}';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Update the home directory of the citizens to the current mail server path';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $changed = 0;
        $total = 0;

        foreach (Citoyen::query()->lazyById() as $citoyen) {
            $total++;
            $homeDirectory = Citoyen::homeDirectoryFor($citoyen->mail);

            if ($citoyen->homeDirectory === $homeDirectory) {
                continue;
            }

            $changed++;

            if ($this->output->isVerbose() || $isDryRun) {
                $this->components->twoColumnDetail($citoyen->homeDirectory, $homeDirectory);
            }

            if (! $isDryRun) {
                $citoyen->timestamps = false;
                $citoyen->update(['homeDirectory' => $homeDirectory]);
            }
        }

        $this->components->info($isDryRun
            ? "{$changed} citoyens sur {$total} à mettre à jour (aucune modification enregistrée)."
            : "{$changed} citoyens sur {$total} mis à jour.");

        return SfCommand::SUCCESS;
    }
}
