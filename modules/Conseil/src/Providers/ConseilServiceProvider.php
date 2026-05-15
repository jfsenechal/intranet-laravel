<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class ConseilServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 64;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([

            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'conseil';
    }
}
