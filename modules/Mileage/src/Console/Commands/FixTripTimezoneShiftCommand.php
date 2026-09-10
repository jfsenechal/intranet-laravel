<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Service\TripAttributeResolver;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FixTripTimezoneShiftCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'mileage:fix-trip-timezone-shift
        {--since=2026-08-04 : Only consider trips created on or after this date}
        {--until= : Only consider trips created before this date}
        {--dry-run : List the trips that would be corrected without writing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Put back on the Belgian clock the trip dates that Filament stored converted to UTC';

    /**
     * Execute the console command.
     *
     * `departure_date` and `arrival_date` hold a Belgian wall clock, not an
     * instant: ten years of trips are stored as the date and time the
     * beneficiary typed. Between the commit that called
     * `FilamentTimezone::set()` and the fix on `TripForm`, the date-time
     * pickers converted their input from Europe/Brussels to UTC, so an
     * external trip encoded as leaving on 08-09 at 00h00 landed in the
     * database as 07-09 22:00 and was then read back a day early by the
     * exports, the rate resolver and the mismatch report.
     *
     * Only external trips are affected: on an internal trip the time inputs
     * are hidden, and Filament does not apply its timezone to a picker that
     * stores no time.
     *
     * This is a one-off repair of that window — running it twice would shift
     * the same trips a second time, hence the `--since` / `--until` bounds and
     * `--dry-run`.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $timezone = config('app.display_timezone');

        $since = Carbon::parse((string) $this->option('since'), config('app.timezone'))->startOfDay();
        $until = $this->option('until') !== null
            ? Carbon::parse((string) $this->option('until'), config('app.timezone'))->startOfDay()
            : null;

        $corrected = [];
        $rateChanges = [];

        Trip::query()
            ->whereNotNull('arrival_date')
            ->where('created_at', '>=', $since)
            ->when($until instanceof CarbonInterface, fn ($query) => $query->where('created_at', '<', $until))
            ->orderBy('id')
            ->chunkById(200, function ($trips) use ($dryRun, $timezone, &$corrected, &$rateChanges): void {
                foreach ($trips as $trip) {
                    $departure = $this->onBelgianClock($trip->departure_date, $timezone);
                    $arrival = $this->onBelgianClock($trip->arrival_date, $timezone);

                    $corrected[] = [
                        $trip->id,
                        $trip->departure_date->format('d-m-Y H:i'),
                        $departure->format('d-m-Y H:i'),
                        $trip->arrival_date->format('d-m-Y H:i'),
                        $arrival->format('d-m-Y H:i'),
                    ];

                    $newRate = $this->rateFor($trip, $departure);

                    if ($newRate instanceof Rate && ! $this->matchesStoredRate($trip, $newRate)) {
                        $rateChanges[] = [$trip->id, $trip->isDeclared() ? 'declared' : 'undeclared', $newRate->amount];
                    }

                    if ($dryRun) {
                        continue;
                    }

                    $trip->departure_date = $departure->format('Y-m-d H:i:s');
                    $trip->arrival_date = $arrival->format('Y-m-d H:i:s');

                    // The rate of a declared trip is a snapshot of what was paid and is
                    // corrected by mileage:verify-trip-rates, never here; an undeclared
                    // trip follows the period its new departure day falls in.
                    if (! $trip->isDeclared()) {
                        app(TripAttributeResolver::class)->setRate($trip);
                    }

                    $trip->saveQuietly();
                }
            });

        return $this->report($corrected, $rateChanges, $dryRun);
    }

    /**
     * The wall clock the beneficiary actually typed: the stored value read as
     * UTC and moved back to the Belgian clock, DST included.
     */
    private function onBelgianClock(CarbonInterface $date, string $timezone): CarbonInterface
    {
        return $date->copy()->setTimezone($timezone);
    }

    private function rateFor(Trip $trip, CarbonInterface $departure): ?Rate
    {
        $probe = $trip->replicate();
        $probe->departure_date = $departure->format('Y-m-d H:i:s');

        return app(TripAttributeResolver::class)->resolveRate($probe);
    }

    private function matchesStoredRate(Trip $trip, Rate $rate): bool
    {
        return (float) $trip->rate === (float) $rate->amount;
    }

    /**
     * @param  array<int, array<int, string|int>>  $corrected
     * @param  array<int, array<int, string|int|float>>  $rateChanges
     */
    private function report(array $corrected, array $rateChanges, bool $dryRun): int
    {
        $this->newLine();

        if ($corrected === []) {
            $this->info('No trip to put back on the Belgian clock.');

            return SfCommand::SUCCESS;
        }

        $this->table(
            ['Trip', 'Departure (stored)', 'Departure (fixed)', 'Arrival (stored)', 'Arrival (fixed)'],
            $corrected
        );

        $count = count($corrected);

        if ($dryRun) {
            $this->info("{$count} trip(s) would be moved back to the Belgian clock. Re-run without --dry-run to write them.");
        } else {
            $this->info("Moved {$count} trip(s) back to the Belgian clock.");
            $this->warn('One-off repair: do not run it again over the same window, the shift would be applied twice.');
        }

        foreach ($rateChanges as [$id, $state, $amount]) {
            $this->warn("Trip #{$id} ({$state}) now falls in a rate period charging {$amount} €/km.");
        }

        return SfCommand::SUCCESS;
    }
}
