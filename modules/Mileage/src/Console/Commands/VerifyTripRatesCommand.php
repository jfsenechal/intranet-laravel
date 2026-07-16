<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
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
        {--fix : Update declared trips whose stored rate does not match their declaration}
        {--skip-omnium : Only verify the rate amount and ignore the omnium}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Verify that declared trips carry the rate and omnium of the declaration they were filed under';

    /**
     * Execute the console command.
     *
     * A declared trip is checked against its own declaration, not against the
     * rate applicable today: the declaration snapshots the rate that was in
     * force when it was filed, and DeclarationCalculator reimburses from that
     * snapshot. Comparing against the rates table would flag every trip whose
     * rate period was edited or superseded after the fact, and "correcting"
     * those would rewrite settled history.
     */
    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $checkOmnium = ! (bool) $this->option('skip-omnium');

        $mismatches = [];
        $missingDeclaration = 0;
        $checked = 0;

        Trip::query()
            ->whereNotNull('declaration_id')
            ->where('declaration_id', '>', 0)
            ->with('declaration')
            ->chunkById(200, function ($trips) use ($fix, $checkOmnium, &$mismatches, &$missingDeclaration, &$checked): void {
                foreach ($trips as $trip) {
                    $checked++;

                    $declaration = $trip->declaration;

                    if (! $declaration instanceof Declaration) {
                        $missingDeclaration++;
                        $this->warn("Trip #{$trip->id} (departure {$trip->departure_date->format('d-m-Y')}): declaration #{$trip->declaration_id} no longer exists");

                        continue;
                    }

                    $expectedOmnium = $this->expectedOmnium($declaration);

                    if ($this->matchesDeclaration($trip, $declaration, $expectedOmnium, $checkOmnium)) {
                        continue;
                    }

                    $declarationId = (int) $trip->declaration_id;
                    $mismatches[$declarationId]['declaration_id'] = $declarationId;
                    $mismatches[$declarationId]['declaration_date'] = $declaration->created_at?->format('d-m-Y') ?? '—';
                    $mismatches[$declarationId]['trips'][] = [
                        $trip->id,
                        $trip->departure_date->format('d-m-Y'),
                        $this->formatAmount($trip->rate),
                        $this->formatAmount($declaration->rate),
                        $checkOmnium ? $this->formatAmount($trip->omnium) : '—',
                        $checkOmnium ? $this->formatAmount($expectedOmnium) : '—',
                    ];

                    if ($fix) {
                        $trip->rate = $declaration->rate;
                        if ($checkOmnium) {
                            $trip->omnium = $expectedOmnium;
                        }
                        $trip->saveQuietly();
                    }
                }
            });

        return $this->report($checked, $missingDeclaration, $mismatches, $fix);
    }

    /**
     * @param  array<int, array{declaration_id: int, declaration_date: string, trips: array<int, array<int, string|int|null>>}>  $mismatches
     */
    private function report(int $checked, int $missingDeclaration, array $mismatches, bool $fix): int
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
                $this->info("Fixed {$mismatchedTrips} trip(s) across {$declarationCount} declaration(s) that did not match their declaration.");
            } else {
                $this->error("Found {$mismatchedTrips} trip(s) across {$declarationCount} declaration(s) that do not match their declaration. Re-run with --fix to correct them.");
            }
        } else {
            $this->info('All declared trips match their declaration.');
        }

        if ($missingDeclaration > 0) {
            $this->warn("{$missingDeclaration} declared trip(s) point at a missing declaration and were skipped.");
        }

        return $mismatches !== [] && ! $fix ? SfCommand::FAILURE : SfCommand::SUCCESS;
    }

    private function matchesDeclaration(Trip $trip, Declaration $declaration, float $expectedOmnium, bool $checkOmnium): bool
    {
        if ($this->formatAmount($trip->rate) !== $this->formatAmount($declaration->rate)) {
            return false;
        }

        if (! $checkOmnium) {
            return true;
        }

        return $this->formatAmount($trip->omnium) === $this->formatAmount($expectedOmnium);
    }

    /**
     * The omnium applicable to the trip: the declaration's omnium only when the
     * declaration is entitled to it, otherwise zero. Mirrors the legacy
     * DeplacementManager::hasOmnium() gate, which left the omnium at 0 for
     * beneficiaries without omnium coverage.
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
