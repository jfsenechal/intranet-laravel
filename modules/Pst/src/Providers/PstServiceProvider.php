<?php

declare(strict_types=1);

namespace AcMarche\Pst\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Pst\Console\Commands\FixCommand;
use AcMarche\Pst\Console\Commands\MeiliCommand;
use AcMarche\Pst\Policies\RegisterPolicies;
use Illuminate\Support\ServiceProvider;

final class PstServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 58;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        $this->bootModule();

        RegisterPolicies::register();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MeiliCommand::class,
                FixCommand::class,
            ]);
        }
    }

    protected function moduleName(): string
    {
        return 'pst';
    }
}
