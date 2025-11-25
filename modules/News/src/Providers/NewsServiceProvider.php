<?php



namespace AcMarche\News\Providers;

use AcMarche\App\Providers\RegisterDatabaseConnectionTrait;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    use RegisterDatabaseConnectionTrait;

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge news config
        $this->mergeConfigFrom(
            __DIR__.'/../config/news.php',
            'news'
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'news');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/news.php' => config_path('news.php'),
        ], 'news-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('news-database.php'),
        ], 'news-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'news-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/news'),
        ], 'news-views');
    }
}
