<?php

declare(strict_types=1);

namespace AcMarche\Agent\Providers;

use AcMarche\Agent\Console\Commands\PruneProfilesCommand;
use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class AgentServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 40;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneProfilesCommand::class,
            ]);
        }

        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'agent';
    }
}
