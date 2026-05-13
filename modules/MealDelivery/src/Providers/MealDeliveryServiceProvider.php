<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\MealDelivery\Console\Commands\PruneAbsencesCommand;
use Illuminate\Support\ServiceProvider;

final class MealDeliveryServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 39;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneAbsencesCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'meal-delivery';
    }
}
