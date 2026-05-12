<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    /**
     * @var array<int, array{table: string, column: string, config: string}>
     */
    private array $targets = [
        ['table' => 'hr_documents', 'column' => 'file_name', 'config' => 'hrm.uploads.documents'],
        ['table' => 'valorizations', 'column' => 'file_name', 'config' => 'hrm.uploads.valorizations'],
        ['table' => 'applications', 'column' => 'file', 'config' => 'hrm.uploads.candidates'],
        ['table' => 'diplomas', 'column' => 'certificate_file', 'config' => 'hrm.uploads.diplomas'],
        ['table' => 'evaluations', 'column' => 'file1_name', 'config' => 'hrm.uploads.evaluations'],
        ['table' => 'evaluations', 'column' => 'file2_name', 'config' => 'hrm.uploads.evaluations'],
        ['table' => 'trainings', 'column' => 'certificate_file', 'config' => 'hrm.uploads.formations'],
        ['table' => 'contracts', 'column' => 'file1_name', 'config' => 'hrm.uploads.contracts'],
        ['table' => 'contracts', 'column' => 'file2_name', 'config' => 'hrm.uploads.contracts'],
        ['table' => 'employees', 'column' => 'candidate_file_name', 'config' => 'hrm.uploads.candidates'],
        ['table' => 'employees', 'column' => 'photo', 'config' => 'hrm.uploads.photos'],
    ];

    public function up(): void
    {
        foreach ($this->targets as $target) {
            $directory = (string) config($target['config']);
            $column = $target['column'];

            DB::connection($this->connection)
                ->table($target['table'])
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->where($column, 'NOT LIKE', $directory.'/%')
                ->update([
                    $column => DB::raw("CONCAT('".addslashes($directory)."/', `{$column}`)"),
                ]);
        }
    }
};
