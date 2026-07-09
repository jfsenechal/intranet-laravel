<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Handler\RecipientDeduplicator;
use Illuminate\Console\Command;
use Override;

final class RecipientDedupeCommand extends Command
{
    #[Override]
    protected $signature = 'courrier:recipients:dedupe {--dry-run : Report duplicates without modifying data}';

    #[Override]
    protected $description = 'Merge duplicate recipients sharing the same username and add a unique index on username';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no data will be changed.');
        }

        $deduplicator = new RecipientDeduplicator(
            $dryRun,
            function (string $username, int $canonicalId, int $duplicateCount): void {
                $this->line("  {$username}: keeping #{$canonicalId}, merging {$duplicateCount} duplicate(s)");
            },
        );

        $stats = $deduplicator->run();

        if ($stats['removed'] === 0) {
            $this->info('No duplicate usernames found.');
        } else {
            $this->info("Removed {$stats['removed']} duplicate recipients, repointed {$stats['repointed']} references.");
        }

        $this->ensureUniqueUsernameIndex($dryRun);

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function ensureUniqueUsernameIndex(bool $dryRun): void
    {
        if (RecipientDeduplicator::hasUniqueUsernameIndex()) {
            $this->info('Unique index on username already present.');

            return;
        }

        if ($dryRun) {
            $this->warn('Would add a unique index on recipients.username.');

            return;
        }

        RecipientDeduplicator::addUniqueUsernameIndex();

        $this->info('Added unique index on recipients.username.');
    }
}
