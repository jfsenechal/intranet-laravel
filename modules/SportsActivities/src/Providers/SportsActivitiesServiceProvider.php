<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class SportsActivitiesServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 63;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([

            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'sports-activities';
    }
}
