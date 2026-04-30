<?php

declare(strict_types=1);

namespace AcMarche\App\Providers;

use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    use HooksTrait;
    use ModuleServiceProviderTrait;

    public function register(): void
    {
        $this->registerModuleConfig();

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
            }
        });
    }

    public function boot(): void
    {
        $this->bootModule();
        $this->buttonListModules();
    }

    protected function moduleName(): string
    {
        return 'app';
    }
}
