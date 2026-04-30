<?php

declare(strict_types=1);

namespace AcMarche\Agent\Console\Commands;

use AcMarche\Agent\Models\Profile;
use AcMarche\Security\Repository\LdapRepository;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class PruneProfilesCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'agent:prune-profiles {--dry-run : Show what would be deleted without deleting}';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Delete Agent profiles whose username no longer exists in LDAP';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;

        Profile::query()->chunkById(200, function ($profiles) use (&$deleted, $dryRun): void {
            foreach ($profiles as $profile) {
                if ($this->existsInLdap($profile->username)) {
                    continue;
                }

                $this->info(($dryRun ? '[dry-run] ' : '').'Pruning '.$profile->fullName().' ('.$profile->username.')');

                if (! $dryRun) {
                    $profile->delete();
                }

                $deleted++;
            }
        });

        $this->info(($dryRun ? 'Would prune ' : 'Pruned ').$deleted.' profile(s).');

        return SfCommand::SUCCESS;
    }

    private function existsInLdap(?string $username): bool
    {
        return LdapRepository::existsByUsername($username);
    }
}
