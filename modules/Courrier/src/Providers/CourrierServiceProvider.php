<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Courrier\Console\Commands\MergeCommand;
use AcMarche\Courrier\Console\Commands\SyncCommand;
use AcMarche\Courrier\Policies\RegisterPolicies;
use DirectoryTree\ImapEngine\Laravel\Facades\Imap;
use Illuminate\Support\ServiceProvider;

final class CourrierServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 16;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MergeCommand::class,
                SyncCommand::class,
            ]);
        }

        RegisterPolicies::register();

        $this->bootModule();

        // Register IMAP mailboxes
        $this->registerImapMailboxes();
    }

    protected function moduleName(): string
    {
        return 'courrier';
    }

    /**
     * Register IMAP mailboxes for the courrier module.
     */
    private function registerImapMailboxes(): void
    {
        Imap::register('imap_ville', [
            'host' => config('courrier.imap.ville.host'),
            'port' => config('courrier.imap.ville.port', 993),
            'username' => config('courrier.imap.ville.username'),
            'password' => config('courrier.imap.ville.password'),
            'encryption' => config('courrier.imap.ville.encryption', 'ssl'),
        ]);
    }
}
