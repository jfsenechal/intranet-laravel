<?php

namespace AcMarche\Courrier;

use AcMarche\Courrier\Console\Commands\MergeCommand;
use AcMarche\Courrier\Console\Commands\SyncCommand;
use AcMarche\Courrier\Policies\RegisterPolicies;
use DirectoryTree\ImapEngine\Laravel\Facades\Imap;
use Illuminate\Support\ServiceProvider;

final class CourrierServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge courrier config
        $this->mergeConfigFrom(
            __DIR__.'/../config/courrier.php',
            'courrier'
        );

        // Register database connection from module config
        $this->registerDatabaseConnection();
    }

    /**
     * Bootstrap services.
     */
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

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courrier');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/courrier.php' => config_path('courrier.php'),
        ], 'courrier-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('courrier-database.php'),
        ], 'courrier-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'courrier-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/courrier'),
        ], 'courrier-views');

        // Register IMAP mailboxes
        $this->registerImapMailboxes();
    }

    /**
     * Register IMAP mailboxes for the courrier module.
     */
    protected function registerImapMailboxes(): void
    {
        Imap::register('imap_ville', [
            'host' => config('courrier.imap.ville.host'),
            'port' => config('courrier.imap.ville.port', 993),
            'username' => config('courrier.imap.ville.username'),
            'password' => config('courrier.imap.ville.password'),
            'encryption' => config('courrier.imap.ville.encryption', 'ssl'),
        ]);
    }

    /**
     * Register the module's database connection.
     */
    protected function registerDatabaseConnection(): void
    {
        $connections = require __DIR__.'/../config/database.php';

        foreach ($connections['connections'] ?? [] as $name => $config) {
            config(['database.connections.'.$name => $config]);
        }
    }
}
