<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Services\LegacyRichTextNormalizer;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SfCommand;

/**
 * Wraps RichEditor columns whose content does not start with a block element
 * (legacy plain text or bare inline HTML such as `Ligne 1<br>Ligne 2`) in a
 * paragraph. TipTap loads such values as a single inline node, which prevents
 * the user from adding a new line in the editor.
 */
final class WrapRichTextCommand extends Command
{
    private const CONNECTION = 'maria-hrm';

    protected $signature = 'hrm:wrap-rich-text {--dry-run : List the rows that would be updated without writing anything}';

    protected $description = 'Wrap RichEditor content missing a leading <p> tag so new lines can be added in the editor';

    /**
     * @var array<int, array{table: string, columns: array<int, string>}>
     */
    private array $targets = [
        ['table' => 'contracts', 'columns' => ['college']],
        ['table' => 'internships', 'columns' => ['notes']],
        ['table' => 'absences', 'columns' => ['notes']],
        ['table' => 'teleworks', 'columns' => ['variable_day_reason', 'employee_notes', 'manager_validation_notes', 'hr_notes']],
        ['table' => 'applications', 'columns' => ['notes']],
        ['table' => 'hr_documents', 'columns' => ['notes']],
        ['table' => 'evaluations', 'columns' => ['notes']],
        ['table' => 'deadlines', 'columns' => ['note']],
        ['table' => 'valorizations', 'columns' => ['content']],
        ['table' => 'trainings', 'columns' => ['description']],
        ['table' => 'employees', 'columns' => ['notes']],
        ['table' => 'services', 'columns' => ['notes']],
    ];

    public function handle(LegacyRichTextNormalizer $normalizer): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach ($this->targets as $target) {
            foreach ($target['columns'] as $column) {
                $updated = $this->wrapColumn($normalizer, $target['table'], $column, $isDryRun);

                if ($updated > 0) {
                    $this->line("{$target['table']}.{$column}: {$updated} row(s)");
                }

                $total += $updated;
            }
        }

        if ($total === 0) {
            $this->info('No RichEditor content to wrap.');

            return SfCommand::SUCCESS;
        }

        $this->info($isDryRun
            ? "{$total} row(s) would be wrapped in a paragraph."
            : "Wrapped {$total} row(s) in a paragraph.");

        return SfCommand::SUCCESS;
    }

    /**
     * @return int The number of rows updated, or that would be updated on a dry run.
     */
    private function wrapColumn(LegacyRichTextNormalizer $normalizer, string $table, string $column, bool $isDryRun): int
    {
        $connection = $this->connection();
        $updated = 0;

        $connection->table($table)
            ->select('id', $column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($normalizer, $connection, $table, $column, $isDryRun, &$updated): void {
                foreach ($rows as $row) {
                    $original = (string) $row->{$column};
                    $wrapped = $normalizer->normalizeForEditor($original);

                    if ($wrapped === $original) {
                        continue;
                    }

                    $updated++;

                    if ($isDryRun) {
                        continue;
                    }

                    $connection->table($table)
                        ->where('id', $row->id)
                        ->update([$column => $wrapped]);
                }
            });

        return $updated;
    }

    private function connection(): Connection
    {
        return DB::connection(self::CONNECTION);
    }
}
