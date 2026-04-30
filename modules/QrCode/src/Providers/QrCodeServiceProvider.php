<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

final class QrCodeServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 56;

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
        return 'qrcode';
    }
}
