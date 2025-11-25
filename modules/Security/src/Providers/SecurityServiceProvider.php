<?php



namespace AcMarche\Security\Providers;

use AcMarche\App\Providers\RegisterDatabaseConnectionTrait;
use AcMarche\Security\Console\Commands\CreateUserCommand;
use AcMarche\Security\Console\Commands\SyncUserCommand;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    use RegisterDatabaseConnectionTrait;

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge security config
        $this->mergeConfigFrom(
            __DIR__.'/../config/security.php',
            'security'
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'security');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUserCommand::class,
                SyncUserCommand::class
            ]);
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/security.php' => config_path('security.php'),
        ], 'security-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('security-database.php'),
        ], 'security-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'security-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/security'),
        ], 'security-views');
    }
}
