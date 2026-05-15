<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class GuichetHdvServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 59;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'guichet-hdv';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
