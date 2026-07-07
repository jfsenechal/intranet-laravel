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
        $deleted = Fiche::query()
            ->where('type', FicheTypeEnum::ABSENCE->value)
            ->whereNotNull('date_end')
            ->whereDate('date_end', '<', Carbon::today())
            ->delete();

        $this->info("Removed {$deleted} expired absence fiche(s).");

        return SfCommand::SUCCESS;
    }
}
