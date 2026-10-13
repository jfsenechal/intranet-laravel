<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Repository\RateMismatchRepository;
use AcMarche\Mileage\Service\TripAttributeResolver;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FixDeclarationRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'mileage:fix-declaration-rates
        {--since=2024 : Only consider trips departing in or after this year, as on the rate mismatch report}
        {--dry-run : List the declarations that would be corrected without writing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Put the official rate on the declarations listed by the rate mismatch report';

    /**
     * Execute the console command.
     *
     * Unlike mileage:verify-trip-rates, this rewrites the declaration itself:
     * it is meant to run once the shortfall shown on the rate mismatch report
     * has been paid out, so that the declaration states the official rate and
     * its total matches what the beneficiary actually received.
     *
     * A declaration carries a single rate. When its trips span several rate
     * periods it is split: the trips of the earliest period stay on the
     * original declaration, and each later period moves to a copy of it
     * keeping the original creation date. A declaration holding a trip no
     * period covers cannot be stated at all and is listed for manual handling.
     *
     * The declaration's omnium rate and its trips' rate and omnium are aligned
     * on the same period, so the result also passes mileage:verify-trip-rates.
     */
    public function handle(TripAttributeResolver $tripAttributeResolver): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $since = (int) $this->option('since');

        $corrected = [];
        $split = [];
        $manual = [];

        foreach (RateMismatchRepository::findSince($since) as $mismatch) {
            $declaration = Declaration::query()->with('trips')->find($mismatch['id']);

            if (! $declaration instanceof Declaration) {
                continue;
            }

            $periods = $this->tripsByRatePeriod($declaration, $tripAttributeResolver);
            $beneficiary = "{$declaration->last_name} {$declaration->first_name}";

            if ($periods === null) {
                $manual[] = [
                    $declaration->id,
                    $beneficiary,
                    DeclarationResource::getUrl('view', ['record' => $declaration->id], panel: 'mileage-panel'),
                ];

                continue;
            }

            if (count($periods) === 1) {
                $rate = $periods[0]['rate'];
                $corrected[] = [
                    $declaration->id,
                    $beneficiary,
                    $this->formatAmount($declaration->rate).' → '.$this->formatAmount($rate->amount),
                    $this->formatAmount($declaration->rate_omnium).' → '.$this->formatAmount($rate->omnium),
                    number_format($mismatch['delta'], 2, '.', ''),
                ];

                if (! $dryRun) {
                    $this->applyRate($declaration, $rate, $periods[0]['trips']);
                }

                continue;
            }

            $newIds = $dryRun ? [] : $this->split($declaration, $periods);

            foreach ($periods as $index => $period) {
                $split[] = [
                    $declaration->id,
                    $beneficiary,
                    $index === 0 ? "#{$declaration->id}" : ($dryRun ? 'new' : '#'.$newIds[$index - 1]),
                    $period['rate']->start_date->format('d-m-Y').' – '.($period['rate']->end_date?->format('d-m-Y') ?? '…'),
                    implode(', ', array_map(fn (Trip $trip): int => $trip->id, $period['trips'])),
                    $this->formatAmount($period['rate']->amount),
                ];
            }
        }

        return $this->report($corrected, $split, $manual, $dryRun);
    }

    /**
     * The declaration's trips grouped by the rate period covering their
     * departure date, earliest period first; null when a trip falls outside
     * every period.
     *
     * @return array<int, array{rate: Rate, trips: array<int, Trip>}>|null
     */
    private function tripsByRatePeriod(Declaration $declaration, TripAttributeResolver $tripAttributeResolver): ?array
    {
        $periods = [];

        foreach ($declaration->trips as $trip) {
            $rate = $tripAttributeResolver->resolveRate($trip);

            if (! $rate instanceof Rate) {
                return null;
            }

            $periods[$rate->id] ??= ['rate' => $rate, 'trips' => []];
            $periods[$rate->id]['trips'][] = $trip;
        }

        usort($periods, fn (array $a, array $b): int => $a['rate']->start_date <=> $b['rate']->start_date);

        return $periods;
    }

    /**
     * Keep the earliest period on the declaration and move every later one to
     * a copy of it.
     *
     * @param  array<int, array{rate: Rate, trips: array<int, Trip>}>  $periods
     * @return array<int, int> the ids of the declarations created, in period order
     */
    private function split(Declaration $declaration, array $periods): array
    {
        $work = function () use ($declaration, $periods): array {
            $newIds = [];

            foreach ($periods as $index => $period) {
                $target = $declaration;

                if ($index > 0) {
                    $target = $declaration->replicate();
                    $target->created_at = $declaration->created_at;
                    $target->saveQuietly();
                    $newIds[] = $target->id;
                }

                $this->applyRate($target, $period['rate'], $period['trips']);
            }

            return $newIds;
        };

        $connection = $declaration->getConnection();

        // Wrap in a transaction so a failure leaves no half-split declaration
        // behind, unless the connection is already inside one (e.g. under test).
        if ($connection->getPdo()->inTransaction()) {
            return $work();
        }

        return $connection->transaction($work);
    }

    /**
     * @param  array<int, Trip>  $trips
     */
    private function applyRate(Declaration $declaration, Rate $rate, array $trips): void
    {
        $declaration->rate = $rate->amount;
        $declaration->rate_omnium = $rate->omnium;
        $declaration->saveQuietly();

        $tripOmnium = $declaration->omnium === true ? $rate->omnium : 0;

        foreach ($trips as $trip) {
            $trip->declaration_id = $declaration->id;
            $trip->rate = $rate->amount;
            $trip->omnium = $tripOmnium;
            $trip->saveQuietly();
        }
    }

    /**
     * @param  array<int, array<int, string|int>>  $corrected
     * @param  array<int, array<int, string|int>>  $split
     * @param  array<int, array<int, string|int>>  $manual
     */
    private function report(array $corrected, array $split, array $manual, bool $dryRun): int
    {
        $this->newLine();

        if ($corrected === [] && $split === [] && $manual === []) {
            $this->info('Every declaration carries the official rate.');

            return SfCommand::SUCCESS;
        }

        if ($corrected !== []) {
            $this->table(
                ['Declaration', 'Beneficiary', 'Rate', 'Omnium rate', 'Shortfall (€)'],
                $corrected
            );

            $count = count($corrected);

            if ($dryRun) {
                $this->info("{$count} declaration(s) would take the official rate. Re-run without --dry-run to write them.");
            } else {
                $this->info("Put the official rate on {$count} declaration(s) and their trips.");
            }
        }

        if ($split !== []) {
            $this->newLine();
            $this->table(
                ['Declaration', 'Beneficiary', 'Split into', 'Rate period', 'Trips', 'Rate'],
                $split
            );

            $count = count(array_unique(array_column($split, 0)));

            if ($dryRun) {
                $this->info("{$count} declaration(s) span several rate periods and would be split, one declaration per period. Re-run without --dry-run to write them.");
            } else {
                $this->info("Split {$count} declaration(s) spanning several rate periods, one declaration per period.");
            }
        }

        if ($manual !== []) {
            $this->newLine();
            $this->warn('These declarations hold a trip no rate period covers and must be corrected by hand:');
            $this->table(['Declaration', 'Beneficiary', 'View URL'], $manual);
        }

        return SfCommand::SUCCESS;
    }

    private function formatAmount(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }
}
