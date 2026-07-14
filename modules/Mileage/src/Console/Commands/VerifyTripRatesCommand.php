<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
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
    protected $signature = 'mileage:verify-trip-rates
        {--fix : Update declared trips whose stored rate does not match the applicable rate}
        {--skip-omnium : Only verify the rate amount and ignore the omnium}';

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
        $checkOmnium = ! (bool) $this->option('skip-omnium');

        $mismatches = [];
        $missingRate = 0;
        $checked = 0;

        Trip::query()
            ->whereNotNull('declaration_id')
            ->where('declaration_id', '>', 0)
            ->with('declaration')
            ->chunkById(200, function ($trips) use ($tripAttributeResolver, $fix, $checkOmnium, &$mismatches, &$missingRate, &$checked): void {
                foreach ($trips as $trip) {
                    $checked++;

                    $rate = $tripAttributeResolver->resolveRate($trip);

                    if (! $rate instanceof Rate) {
                        $missingRate++;
                        $this->warn("Trip #{$trip->id} (departure {$trip->departure_date->format('d-m-Y')}): no applicable rate found");

                        continue;
                    }

                    $expectedOmnium = $this->expectedOmnium($trip, $rate);

                    if ($this->rateMatches($trip, $rate, $expectedOmnium, $checkOmnium)) {
                        continue;
                    }

                    $declarationId = (int) $trip->declaration_id;
                    $mismatches[$declarationId]['declaration_id'] = $declarationId;
                    $mismatches[$declarationId]['declaration_date'] = $trip->declaration?->created_at?->format('d-m-Y') ?? '—';
                    $mismatches[$declarationId]['trips'][] = [
                        $trip->id,
                        $trip->departure_date->format('d-m-Y'),
                        $this->formatAmount($trip->rate),
                        $this->formatAmount($rate->amount),
                        $checkOmnium ? $this->formatAmount($trip->omnium) : '—',
                        $checkOmnium ? $this->formatAmount($expectedOmnium) : '—',
                    ];

                    if ($fix) {
                        $trip->rate = $rate->amount;
                        if ($checkOmnium) {
                            $trip->omnium = $expectedOmnium;
                        }
                        $trip->saveQuietly();
                    }
                }
            });

        return $this->report($checked, $missingRate, $mismatches, $fix);
    }

    /**
     * @param  array<int, array{declaration_id: int, declaration_date: string, trips: array<int, array<int, string|int|null>>}>  $mismatches
     */
    private function report(int $checked, int $missingRate, array $mismatches, bool $fix): int
    {
        $this->newLine();
        $this->info("Checked {$checked} declared trip(s).");

        if ($mismatches !== []) {
            $mismatchedTrips = 0;
            $rows = [];

            foreach ($mismatches as $mismatch) {
                $tripCount = count($mismatch['trips']);
                $mismatchedTrips += $tripCount;

                $rows[] = [
                    $mismatch['declaration_id'],
                    $mismatch['declaration_date'],
                    $tripCount,
                    implode(', ', array_column($mismatch['trips'], 0)),
                    DeclarationResource::getUrl('view', ['record' => $mismatch['declaration_id']], panel: 'mileage-panel'),
                ];
            }

            $this->newLine();
            $this->table(
                ['Declaration', 'Date', 'Trips', 'Trip IDs', 'View URL'],
                $rows
            );

            $declarationCount = count($mismatches);
            if ($fix) {
                $this->info("Fixed {$mismatchedTrips} trip(s) across {$declarationCount} declaration(s) with an incorrect rate.");
            } else {
                $this->error("Found {$mismatchedTrips} trip(s) across {$declarationCount} declaration(s) with an incorrect rate. Re-run with --fix to correct them.");
            }
        } else {
            $this->info('All declared trips carry the correct rate.');
        }

        if ($missingRate > 0) {
            $this->warn("{$missingRate} declared trip(s) have no applicable rate and were skipped.");
        }

        return $mismatches !== [] && ! $fix ? SfCommand::FAILURE : SfCommand::SUCCESS;
    }

    private function rateMatches(Trip $trip, Rate $rate, float $expectedOmnium, bool $checkOmnium): bool
    {
        if ($this->formatAmount($trip->rate) !== $this->formatAmount($rate->amount)) {
            return false;
        }

        if (! $checkOmnium) {
            return true;
        }

        return $this->formatAmount($trip->omnium) === $this->formatAmount($expectedOmnium);
    }

    /**
     * The omnium applicable to the trip: the rate's omnium only when the
     * declaration is entitled to it, otherwise zero. Mirrors the legacy
     * DeplacementManager::hasOmnium() gate, which left the omnium at 0 for
     * beneficiaries without omnium coverage.
     */
    private function expectedOmnium(Trip $trip, Rate $rate): float
    {
        if ($trip->declaration?->omnium !== true) {
            return 0.0;
        }

        return (float) $rate->omnium;
    }

    private function formatAmount(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }
}
