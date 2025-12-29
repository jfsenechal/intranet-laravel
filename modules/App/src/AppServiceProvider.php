<?php

namespace AcMarche\App;

use AcMarche\App\Traits\HooksTrait;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    use HooksTrait;

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge security config
        $this->mergeConfigFrom(
            __DIR__.'/../config/app.php',
            'app'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'app');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/app.php' => config_path('app.php'),
        ], 'app-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/app'),
        ], 'app-views');

        // Publish public assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/app'),
        ], 'app-assets');

        $this->buttonListModules();
    }
}
