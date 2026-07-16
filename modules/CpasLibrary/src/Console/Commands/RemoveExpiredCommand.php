<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Console\Commands;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Models\Fiche;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class RemoveExpiredCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'cpas-library:remove-expired';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Remove absence fiches whose end date is in the past';

    public function handle(): int
    {
        $expired = Fiche::query()
            ->where('type', FicheTypeEnum::ABSENCE->value)
            ->whereNotNull('date_end')
            ->whereDate('date_end', '<', Carbon::today())
            ->get();

        // Deleted one by one rather than with a mass delete, so that the model's
        // `deleting` hook runs and removes each fiche's file from the disk.
        $expired->each(fn (Fiche $fiche): ?bool => $fiche->delete());

        $deleted = $expired->count();

        $this->info("Removed {$deleted} expired absence fiche(s).");

        return SfCommand::SUCCESS;
    }
}
