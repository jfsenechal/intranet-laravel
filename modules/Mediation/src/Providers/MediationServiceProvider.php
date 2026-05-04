<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class MediationServiceProvider extends ServiceProvider
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
        return 'mediation';
    }
}
