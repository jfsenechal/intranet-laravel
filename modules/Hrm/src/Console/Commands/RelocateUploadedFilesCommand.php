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
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SfCommand;

/**
 * Legacy uploads were stored in folders whose names differ from the canonical
 * directories now used by both the database references and new Filament uploads
 * (see config('hrm.uploads.*')). The database already holds the canonical paths,
 * so this command locates each referenced file anywhere under the HRM tree by
 * its (globally unique) basename and moves it to the path referenced in the
 * database, leaving future uploads untouched.
 */
final class RelocateUploadedFilesCommand extends Command
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

    /**
     * Root directory (relative to the disk) under which every HRM upload lives.
     */
    private const string HRM_ROOT = 'hrm';

    /**
     * Canonical directory (as referenced in the database) mapped to the legacy
     * directories that historically held its files. Used to break ties when a
     * basename exists in more than one folder.
     *
     * @var array<string, list<string>>
     */
    private const array LEGACY_ALIASES = [
        'hrm/contracts' => ['hrm/contrats'],
        'hrm/evaluations' => ['hrm/valorizations'],
        'hrm/valorizations' => ['hrm/documents'],
        'hrm/candidates' => ['hrm/candidatures'],
    ];

    protected $signature = 'hrm:relocate-uploads {--dry-run : List the planned moves without touching any file}';

    protected $description = 'Move legacy HRM upload files into the canonical path referenced by the database';

    /**
     * Per-disk index of basename => list of physical paths, built lazily.
     *
     * @var array<string, array<string, list<string>>>
     */
    private array $indexes = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $alreadyPresent = 0;
        $moved = 0;
        $ambiguous = [];
        $unresolved = [];

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
                    ->each(function (Model $record) use ($field, $disk, $check, $dryRun, &$alreadyPresent, &$moved, &$ambiguous, &$unresolved): void {
                        $target = (string) $record->getAttribute($field);

                        if ($disk->exists($target)) {
                            $alreadyPresent++;

                            return;
                        }

                        $candidates = $this->index($disk, $check['disk'])[basename($target)] ?? [];

                        $row = [
                            class_basename($check['model']),
                            (string) $record->getKey(),
                            $field,
                            $check['disk'],
                            $target,
                        ];

                        if ($candidates === []) {
                            $unresolved[] = $row;

                            return;
                        }

                        $source = $this->pickSource($target, $candidates);

                        if ($source === null) {
                            $ambiguous[] = [...$row, implode(' | ', $candidates)];

                            return;
                        }

                        if (! $dryRun) {
                            $disk->move($source, $target);
                            $this->refreshIndex($check['disk'], basename($target), $source, $target);
                        }

                        $moved++;
                    });
            }
        }

        $verb = $dryRun ? 'would be moved' : 'moved';
        $this->info("{$alreadyPresent} file(s) already in place.");
        $this->info("{$moved} file(s) {$verb} from a legacy folder.");

        if ($ambiguous !== []) {
            $this->warn(count($ambiguous).' referenced file(s) match several source files and were skipped:');
            $this->table(['Model', 'ID', 'Field', 'Disk', 'Target', 'Candidates'], $ambiguous);
        }

        if ($unresolved !== []) {
            $this->error(count($unresolved).' referenced file(s) could not be located anywhere:');
            $this->table(['Model', 'ID', 'Field', 'Disk', 'Path'], $unresolved);
        }

        return $ambiguous === [] && $unresolved === [] ? SfCommand::SUCCESS : SfCommand::FAILURE;
    }

    /**
     * Choose the single source file for a target among the indexed candidates.
     * A lone candidate is used as-is; ties are broken toward the target's known
     * legacy folder, and anything still ambiguous returns null to be reported.
     *
     * @param  list<string>  $candidates
     */
    private function pickSource(string $target, array $candidates): ?string
    {
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        $legacyDirs = self::LEGACY_ALIASES[dirname($target)] ?? [];
        $preferred = array_values(array_filter(
            $candidates,
            static fn (string $path): bool => in_array(dirname($path), $legacyDirs, true),
        ));

        return count($preferred) === 1 ? $preferred[0] : null;
    }

    /**
     * Build (once per disk) an index of basename => physical paths under the HRM root.
     *
     * @return array<string, list<string>>
     */
    private function index(Filesystem $disk, string $diskName): array
    {
        if (isset($this->indexes[$diskName])) {
            return $this->indexes[$diskName];
        }

        $index = [];

        foreach ($disk->allFiles(self::HRM_ROOT) as $path) {
            $index[basename($path)][] = $path;
        }

        return $this->indexes[$diskName] = $index;
    }

    /**
     * Keep the in-memory index in sync after a file has been moved.
     */
    private function refreshIndex(string $diskName, string $basename, string $from, string $to): void
    {
        $paths = array_values(array_filter(
            $this->indexes[$diskName][$basename] ?? [],
            static fn (string $path): bool => $path !== $from,
        ));
        $paths[] = $to;
        $this->indexes[$diskName][$basename] = $paths;
    }
}
