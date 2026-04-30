<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Hrm\Console\Commands\MigrationCommand;
use AcMarche\Hrm\Console\Commands\ReminderCommand;
use AcMarche\Hrm\Console\Commands\SyncEmployeeCommand;
use AcMarche\Hrm\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class HrmServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 6;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrationCommand::class,
                ReminderCommand::class,
                SyncEmployeeCommand::class,
            ]);
        }
        $this->bootModule();
        $this->registerPolicies();
    }

    protected function moduleName(): string
    {
        return 'hrm';
    }

    /**
     * Register the policies for the module.
     */
    private function registerPolicies(): void
    {
        Gate::define('hrm-administrator', function (User $user): bool {
            if ($user->isAdministrator()) {
                return true;
            }

            return $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value);
        });

        Gate::define('hrm-director', function (User $user): bool {
            if ($user->isAdministrator()) {
                return true;
            }

            return $user->hasOneOfThisRoles([
                RolesEnum::ROLE_GRH_DIRECTION->value,
            ]);
        });
    }
}
