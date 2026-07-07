<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\CpasLibrary\Console\Commands\ReminderCommand;
use AcMarche\CpasLibrary\Console\Commands\RemoveExpiredCommand;
use AcMarche\CpasLibrary\Console\Commands\ResumeCommand;
use Illuminate\Support\ServiceProvider;

final class CpasLibraryServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 52;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReminderCommand::class,
                RemoveExpiredCommand::class,
                ResumeCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'cpas-library';
    }
}
