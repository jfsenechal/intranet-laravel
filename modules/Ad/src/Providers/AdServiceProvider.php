<?php

declare(strict_types=1);

namespace AcMarche\Ad\Providers;

use AcMarche\Ad\Console\Commands\RemoveIneligibleSubscribersCommand;
use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class AdServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 62;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RemoveIneligibleSubscribersCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'ad';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
