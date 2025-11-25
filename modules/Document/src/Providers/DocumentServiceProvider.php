<?php



namespace AcMarche\Document\Providers;

use Illuminate\Support\ServiceProvider;

class DocumentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge document config
        $this->mergeConfigFrom(
            __DIR__.'/../config/document.php',
            'document'
        );

        // Register database connection from module config
        $this->registerDatabaseConnection();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'document');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/document.php' => config_path('document.php'),
        ], 'document-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('document-database.php'),
        ], 'document-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'document-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/document'),
        ], 'document-views');
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
