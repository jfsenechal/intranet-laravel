<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class AgendaEchevinServiceProvider extends ServiceProvider
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
        return 'agenda_echevin';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
