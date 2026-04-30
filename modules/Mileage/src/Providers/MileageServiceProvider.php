<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Mileage\Console\Commands\MigrationCommand;
use Illuminate\Support\ServiceProvider;

final class MileageServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 13;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrationCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'mileage';
    }
}
