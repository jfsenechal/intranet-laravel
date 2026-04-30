<?php

declare(strict_types=1);

namespace AcMarche\Pst\Console\Commands;

use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\Odd;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Partner;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Override;

final class MeiliCommand extends Command
{
    #[Override]
    protected $signature = 'pst:meili {--flush : Flush all indexes before importing}';

    #[Override]
    protected $description = 'Import all searchable models into Scout (Meilisearch)';

    /**
     * @var array<int, class-string>
     */
    private array $searchableModels = [
        Action::class,
        Odd::class,
        OperationalObjective::class,
        Partner::class,
        Service::class,
        StrategicObjective::class,
        User::class,
    ];

    public function handle(): int
    {
        $this->info('Starting Scout import...');

        foreach ($this->searchableModels as $model) {
            $modelName = class_basename($model);

            if ($this->option('flush')) {
                $this->components->task("Flushing {$modelName}", function () use ($model): void {
                    Artisan::call('scout:flush', ['model' => $model]);
                });
            }

            $this->components->task("Importing {$modelName}", function () use ($model): void {
                Artisan::call('scout:import', ['model' => $model]);
            });
        }

        $this->newLine();
        $this->info('All searchable models have been imported.');

        return self::SUCCESS;
    }
}
