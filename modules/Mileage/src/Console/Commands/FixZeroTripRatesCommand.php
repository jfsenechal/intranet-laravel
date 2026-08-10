<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FixZeroTripRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'mileage:fix-zero-trip-rates
        {--dry-run : List the trips that would be corrected without writing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Copy the declaration rate onto declared trips stored with a zero rate';

    /**
     * Execute the console command.
     *
     * A declared trip stored with a rate of 0 is a bookkeeping hole, not a
     * shortfall: DeclarationCalculator reimburses the whole declaration from
     * declaration->rate, so the beneficiary was paid correctly and this
     * command changes no reimbursement. It happens when the trip is encoded
     * before the rate period covering its departure date exists, leaving
     * TripAttributeResolver::setRate() with no rate to apply; the declaration
     * filed afterwards then carries the right rate while its trips still read
     * 0, which makes every per-trip listing and export understate them.
     *
     * The declaration is the source of truth, exactly as in
     * mileage:verify-trip-rates: the rate it snapshotted is what was actually
     * paid, so it is copied down rather than resolved afresh from the rates
     * table, which may have moved since.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $corrected = [];
        $skipped = 0;

        Trip::query()
            ->whereNotNull('declaration_id')
            ->where('declaration_id', '>', 0)
            ->where(function ($query): void {
                $query->where('rate', 0)->orWhereNull('rate');
            })
            ->with('declaration')
            ->chunkById(200, function ($trips) use ($dryRun, &$corrected, &$skipped): void {
                foreach ($trips as $trip) {
                    $declaration = $trip->declaration;

                    if (! $declaration instanceof Declaration) {
                        $skipped++;
                        $this->warn("Trip #{$trip->id}: declaration #{$trip->declaration_id} no longer exists — skipped");

                        continue;
                    }

                    if ((float) $declaration->rate === 0.0) {
                        $skipped++;
                        $this->warn("Trip #{$trip->id}: declaration #{$declaration->id} carries a zero rate too — skipped");

                        continue;
                    }

                    $omnium = $this->expectedOmnium($declaration);

                    $corrected[] = [
                        $trip->id,
                        $declaration->id,
                        $trip->departure_date->format('d-m-Y'),
                        $trip->distance,
                        $this->formatAmount($declaration->rate),
                        $this->formatAmount($omnium),
                    ];

                    if (! $dryRun) {
                        $trip->rate = $declaration->rate;
                        $trip->omnium = $omnium;
                        $trip->saveQuietly();
                    }
                }
            });

        return $this->report($corrected, $skipped, $dryRun);
    }

    /**
     * @param  array<int, array<int, string|int|null>>  $corrected
     */
    private function report(array $corrected, int $skipped, bool $dryRun): int
    {
        $this->newLine();

        if ($corrected === []) {
            $this->info('No declared trip carries a zero rate.');

            return $this->exitCode($skipped);
        }

        $this->table(
            ['Trip', 'Declaration', 'Departure', 'Km', 'Rate applied', 'Omnium applied'],
            $corrected
        );

        $tripCount = count($corrected);
        $declarationCount = count(array_unique(array_column($corrected, 1)));

        if ($dryRun) {
            $this->info("{$tripCount} trip(s) across {$declarationCount} declaration(s) would take their declaration rate. Re-run without --dry-run to write them.");
        } else {
            $this->info("Aligned {$tripCount} trip(s) across {$declarationCount} declaration(s) on their declaration rate.");
            $this->line('Reimbursements are unchanged: they are computed from the declaration, not from the stored trip rate.');
        }

        return $this->exitCode($skipped);
    }

    private function exitCode(int $skipped): int
    {
        if ($skipped > 0) {
            $this->warn("{$skipped} trip(s) had no usable declaration rate and were left at zero.");
        }

        return SfCommand::SUCCESS;
    }

    /**
     * The omnium applicable to the trip: the declaration's omnium only when the
     * declaration is entitled to it, otherwise zero. Mirrors the gate applied
     * by mileage:verify-trip-rates, so a trip fixed here also passes there.
     */
    private function expectedOmnium(Declaration $declaration): float
    {
        if ($declaration->omnium !== true) {
            return 0.0;
        }

        return (float) $declaration->rate_omnium;
    }

    private function formatAmount(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }
}
