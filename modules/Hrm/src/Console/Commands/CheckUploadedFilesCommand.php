<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Models\Application;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\HrDocument;
use AcMarche\Hrm\Models\Training;
use AcMarche\Hrm\Models\Valorization;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SfCommand;

final class CheckUploadedFilesCommand extends Command
{
    /**
     * Map of models to their file-holding attributes and the disk they live on.
     *
     * @var list<array{model: class-string<Model>, disk: string, fields: list<string>}>
     */
    private const array CHECKS = [
        ['model' => Contract::class, 'disk' => 'local', 'fields' => ['file1_name', 'file2_name']],
        ['model' => Evaluation::class, 'disk' => 'local', 'fields' => ['file1_name', 'file2_name']],
        ['model' => HrDocument::class, 'disk' => 'local', 'fields' => ['file_name']],
        ['model' => Diploma::class, 'disk' => 'local', 'fields' => ['certificate_file']],
        ['model' => Application::class, 'disk' => 'local', 'fields' => ['file']],
        ['model' => Valorization::class, 'disk' => 'local', 'fields' => ['file_name']],
        ['model' => Training::class, 'disk' => 'local', 'fields' => ['certificate_file']],
        ['model' => Employee::class, 'disk' => 'public', 'fields' => ['photo']],
        ['model' => Employee::class, 'disk' => 'local', 'fields' => ['candidate_file_name']],
    ];

    protected $signature = 'hrm:check-uploads';

    protected $description = 'Check that every file referenced by HRM models still exists on its storage disk';

    public function handle(): int
    {
        $checked = 0;
        $missing = [];

        foreach (self::CHECKS as $check) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $check['model'];
            $disk = Storage::disk($check['disk']);

            foreach ($check['fields'] as $field) {
                $modelClass::query()
                    ->whereNotNull($field)
                    ->where($field, '!=', '')
                    ->select(['id', $field])
                    ->lazyById()
                    ->each(function (Model $record) use ($field, $disk, $check, &$checked, &$missing): void {
                        $path = (string) $record->getAttribute($field);
                        $checked++;

                        if (! $disk->exists($path)) {
                            $missing[] = [
                                class_basename($check['model']),
                                (string) $record->getKey(),
                                $field,
                                $check['disk'],
                                $path,
                            ];
                        }
                    });
            }
        }

        $this->info("Checked {$checked} referenced file(s).");

        if ($missing === []) {
            $this->info('All referenced files exist.');

            return SfCommand::SUCCESS;
        }

        $this->error(count($missing).' referenced file(s) are missing:');
        $this->table(['Model', 'ID', 'Field', 'Disk', 'Path'], $missing);

        return SfCommand::FAILURE;
    }
}
