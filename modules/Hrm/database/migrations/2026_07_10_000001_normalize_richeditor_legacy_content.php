<?php

declare(strict_types=1);

use AcMarche\Hrm\Services\LegacyRichTextNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * These columns were plain-text textareas historically, displayed with nl2br().
 * They are now RichEditor (HTML) fields. This backfill converts the remaining
 * legacy plain-text rows to HTML so old and new values render identically as
 * raw HTML — both in infolists and inside the RichEditor edit form.
 *
 * Rows that already contain HTML (created via the RichEditor) are left untouched.
 */
return new class extends Migration
{
    protected $connection = 'maria-hrm';

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

    public function up(): void
    {
        $normalizer = new LegacyRichTextNormalizer;

        foreach ($this->targets as $target) {
            foreach ($target['columns'] as $column) {
                $this->normalizeColumn($normalizer, $target['table'], $column);
            }
        }
    }

    private function normalizeColumn(LegacyRichTextNormalizer $normalizer, string $table, string $column): void
    {
        $connection = DB::connection($this->connection);

        $connection->table($table)
            ->select('id', $column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($normalizer, $connection, $table, $column): void {
                foreach ($rows as $row) {
                    $original = (string) $row->{$column};
                    $normalized = $normalizer->normalize($original);

                    if ($normalized === $original) {
                        continue;
                    }

                    $connection->table($table)
                        ->where('id', $row->id)
                        ->update([$column => $normalized]);
                }
            });
    }
};
