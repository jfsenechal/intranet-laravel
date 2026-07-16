<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\EmailManagement\Console\Commands\SieveCheckCommand;
use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Sieve\SieveEmploye;
use Illuminate\Support\ServiceProvider;

final class EmailManagementServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 5;

    public function register(): void
    {
        $this->registerModuleConfig();

        // Scoped rather than singleton: these hold an open server connection, which must not
        // be carried between requests by an Octane worker.
        $this->app->scoped(ImapEmploye::class, static fn (): ImapEmploye => ImapEmploye::fromConfig());
        $this->app->scoped(SieveEmploye::class, static fn (): SieveEmploye => SieveEmploye::fromConfig());
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SieveCheckCommand::class,
            ]);
        }
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'email-management';
    }
}
