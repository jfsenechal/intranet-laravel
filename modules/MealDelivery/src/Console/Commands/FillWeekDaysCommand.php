<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Console\Commands;

use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\WeekDaysBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FillWeekDaysCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'meal-delivery:fill-week-days
        {--from=2026-09-01 : Only weeks starting strictly after this date}
        {--dry-run : Show the weeks that would change without saving them}
        {--skip-archived : Leave archived weeks untouched}';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Complete the weeks starting after a given date to seven days, Monday to Sunday';

    public function handle(WeekDaysBuilder $weekDaysBuilder): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $from = CarbonImmutable::parse((string) $this->option('from'))->format('Y-m-d');

        $this->info("Weeks starting after {$from}.");

        $weeks = Week::query()
            ->whereDate('first_day', '>', $from)
            ->when(
                $this->option('skip-archived'),
                fn (Builder $query): Builder => $query->where('is_archived', false),
            )
            ->orderBy('first_day')
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($weeks as $week) {
            if ($week->first_day === null) {
                $this->warn("Week #{$week->id} has no first_day, skipped.");
                $skipped++;

                continue;
            }

            $current = $weekDaysBuilder->normalize((array) ($week->days ?? []));
            $completed = $weekDaysBuilder->complete($current, $week->first_day);

            if ($completed === $current) {
                continue;
            }

            $this->line(sprintf(
                'Week #%d (%s): %d day(s) -> %d, adding %s',
                $week->id,
                $week->first_day->format('Y-m-d'),
                count($current),
                count($completed),
                implode(', ', array_values(array_diff($completed, $current))),
            ));

            if (! $isDryRun) {
                $week->days = $completed;
                $week->save();
            }

            $updated++;
        }

        $this->info($isDryRun
            ? "{$updated} week(s) would be completed to seven days ({$skipped} skipped)."
            : "{$updated} week(s) completed to seven days ({$skipped} skipped).");

        return SfCommand::SUCCESS;
    }
}
