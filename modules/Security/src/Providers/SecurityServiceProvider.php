<?php

declare(strict_types=1);

namespace AcMarche\Security\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Security\Console\Commands\CreateUserCommand;
use AcMarche\Security\Console\Commands\MigrationRoleCommand;
use AcMarche\Security\Console\Commands\SyncUserCommand;
use Illuminate\Support\ServiceProvider;

final class SecurityServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 17;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUserCommand::class,
                SyncUserCommand::class,
                MigrationRoleCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'security';
    }
}
