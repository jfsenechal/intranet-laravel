<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ExpireNewHiresCommand extends Command
{
    protected $signature = 'hrm:expire-new-hires';

    protected $description = 'Clear the is_new_hire flag for employees hired more than one month ago';

    public function handle(): int
    {
        $threshold = Carbon::today()->subMonth();

        $updated = Employee::query()
            ->where('is_new_hire', true)
            ->whereNotNull('hired_at')
            ->whereDate('hired_at', '<=', $threshold)
            ->update(['is_new_hire' => false]);

        $this->info("Expired is_new_hire flag on {$updated} employee(s).");

        return SfCommand::SUCCESS;
    }
}
