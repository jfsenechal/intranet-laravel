<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Override;

final class MigrationCommand extends Command
{
    #[Override]
    protected $signature = 'hrm:migration';

    #[Override]
    protected $description = 'Migration: convert oui/non strings to boolean values';

    private int $absenceCount = 0;

    private int $contractCount = 0;

    public function handle(): int
    {
        $booleanColumns = ['certimed', 'has_resumed', 'clock_updated', 'acropole', 'agent_file'];

        foreach ($booleanColumns as $column) {
            $updated = DB::connection('maria-hrm')
                ->table('absences')
                ->where($column, 'oui')
                ->update([$column => true]);

            $updated += DB::connection('maria-hrm')
                ->table('absences')
                ->where($column, 'non')
                ->update([$column => false]);

            $this->absenceCount += $updated;
            $this->info("Absences: converted {$updated} rows for column '{$column}'");
        }

        $updated = DB::connection('maria-hrm')
            ->table('contracts')
            ->where('is_replacement', 'oui')
            ->update(['is_replacement' => true]);

        $updated += DB::connection('maria-hrm')
            ->table('contracts')
            ->where('is_replacement', 'non')
            ->update(['is_replacement' => false]);

        $this->contractCount = $updated;
        $this->info("Contracts: converted {$updated} rows for column 'is_replacement'");

        $this->info('Migration completed successfully!');
        $this->displaySummary();

        return self::SUCCESS;
    }

    private function displaySummary(): void
    {
        $this->table(
            ['Table', 'Rows updated'],
            [
                ['absences', $this->absenceCount],
                ['contracts', $this->contractCount],
            ],
        );
    }
}
