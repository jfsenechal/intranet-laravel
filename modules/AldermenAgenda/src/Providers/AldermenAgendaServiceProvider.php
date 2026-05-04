<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class AldermenAgendaServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 20;

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
        return 'aldermen-agenda';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
