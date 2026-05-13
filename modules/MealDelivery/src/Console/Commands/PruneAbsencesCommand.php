<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Console\Commands;

use AcMarche\MealDelivery\Models\Absence;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class PruneAbsencesCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'meal-delivery:prune-absences';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Remove absences whose end_date is in the past';

    public function handle(): int
    {
        $deleted = Absence::query()
            ->whereDate('end_date', '<', now()->toDateString())
            ->delete();

        $this->info("Removed {$deleted} obsolete absence(s).");

        return SfCommand::SUCCESS;
    }
}
