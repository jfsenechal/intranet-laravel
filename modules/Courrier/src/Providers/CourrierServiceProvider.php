<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Courrier\Console\Commands\CheckAttachmentFilesCommand;
use AcMarche\Courrier\Console\Commands\ListPendingNotificationsCommand;
use AcMarche\Courrier\Console\Commands\MeiliIndexerCommand;
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
                CheckAttachmentFilesCommand::class,
                ListPendingNotificationsCommand::class,
                MeiliIndexerCommand::class,
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
        Imap::register('imap_bgm', [
            'host' => config('courrier.imap.bgm.host'),
            'port' => config('courrier.imap.bgm.port', 993),
            'username' => config('courrier.imap.bgm.username'),
            'password' => config('courrier.imap.bgm.password'),
            'encryption' => config('courrier.imap.bgm.encryption', 'ssl'),
        ]);
        Imap::register('imap_cpas', [
            'host' => config('courrier.imap.cpas.host'),
            'port' => config('courrier.imap.cpas.port', 993),
            'username' => config('courrier.imap.cpas.username'),
            'password' => config('courrier.imap.cpas.password'),
            'encryption' => config('courrier.imap.cpas.encryption', 'ssl'),
        ]);
        Imap::register('imap_ville', [
            'host' => config('courrier.imap.ville.host'),
            'port' => config('courrier.imap.ville.port', 993),
            'username' => config('courrier.imap.ville.username'),
            'password' => config('courrier.imap.ville.password'),
            'encryption' => config('courrier.imap.ville.encryption', 'ssl'),
        ]);
    }
}
