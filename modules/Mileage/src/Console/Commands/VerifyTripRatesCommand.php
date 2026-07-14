<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Service\TripAttributeResolver;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class VerifyTripRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'mileage:verify-trip-rates {--fix : Update declared trips whose stored rate does not match the applicable rate}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Verify that declared trips carry the rate and omnium applicable to their departure date';

    /**
     * Execute the console command.
     */
    public function handle(TripAttributeResolver $tripAttributeResolver): int
    {
        $fix = (bool) $this->option('fix');

        $mismatches = [];
        $missingRate = 0;
        $checked = 0;

        Trip::query()
            ->whereNotNull('declaration_id')
            ->where('declaration_id', '>', 0)
            ->with('declaration')
            ->chunkById(200, function ($trips) use ($tripAttributeResolver, $fix, &$mismatches, &$missingRate, &$checked): void {
                foreach ($trips as $trip) {
                    $checked++;

                    $rate = $tripAttributeResolver->resolveRate($trip);

                    if (! $rate instanceof Rate) {
                        $missingRate++;
                        $this->warn("Trip #{$trip->id} (departure {$trip->departure_date->format('d-m-Y')}): no applicable rate found");

                        continue;
                    }

                    if ($this->rateMatches($trip, $rate)) {
                        continue;
                    }

                    $mismatches[] = [
                        $trip->id,
                        $trip->declaration_id,
                        $trip->departure_date->format('d-m-Y'),
                        $this->formatAmount($trip->rate),
                        $this->formatAmount($rate->amount),
                        $this->formatAmount($trip->omnium),
                        $this->formatAmount($rate->omnium),
                    ];

                    if ($fix) {
                        $trip->rate = $rate->amount;
                        $trip->omnium = $rate->omnium;
                        $trip->saveQuietly();
                    }
                }
            });

        return $this->report($checked, $missingRate, $mismatches, $fix);
    }

    /**
     * @param  array<int, array<int, string|int|null>>  $mismatches
     */
    private function report(int $checked, int $missingRate, array $mismatches, bool $fix): int
    {
        $this->newLine();
        $this->info("Checked {$checked} declared trip(s).");

        if ($mismatches !== []) {
            $this->newLine();
            $this->table(
                ['Trip', 'Declaration', 'Departure', 'Stored rate', 'Expected rate', 'Stored omnium', 'Expected omnium'],
                $mismatches
            );

            $count = count($mismatches);
            if ($fix) {
                $this->info("Fixed {$count} trip(s) with an incorrect rate.");
            } else {
                $this->error("Found {$count} trip(s) with an incorrect rate. Re-run with --fix to correct them.");
            }
        } else {
            $this->info('All declared trips carry the correct rate.');
        }

        if ($missingRate > 0) {
            $this->warn("{$missingRate} declared trip(s) have no applicable rate and were skipped.");
        }

        return $mismatches !== [] && ! $fix ? SfCommand::FAILURE : SfCommand::SUCCESS;
    }

    private function rateMatches(Trip $trip, Rate $rate): bool
    {
        return $this->formatAmount($trip->rate) === $this->formatAmount($rate->amount)
            && $this->formatAmount($trip->omnium) === $this->formatAmount($rate->omnium);
    }

    private function formatAmount(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }
}
