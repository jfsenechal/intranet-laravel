<?php

namespace AcMarche\Publication;

use AcMarche\App\Providers\RegisterDatabaseConnectionTrait;
use Illuminate\Support\ServiceProvider;

final class PublicationServiceProvider extends ServiceProvider
{
    use RegisterDatabaseConnectionTrait;

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge publication config
        $this->mergeConfigFrom(
            __DIR__.'/../config/publication.php',
            'publication'
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'publication');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/publication.php' => config_path('publication.php'),
        ], 'publication-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('publication-database.php'),
        ], 'publication-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'publication-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/publication'),
        ], 'publication-views');
    }
}
