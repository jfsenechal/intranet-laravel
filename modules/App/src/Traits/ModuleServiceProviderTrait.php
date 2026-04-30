<?php

declare(strict_types=1);

namespace AcMarche\App\Traits;

use ReflectionClass;

trait ModuleServiceProviderTrait
{
    /**
     * The module name (e.g. 'courrier', 'hrm', 'news').
     */
    abstract protected function moduleName(): string;

    /**
     * The base path of the module (directory containing config/, database/, resources/, routes/).
     */
    protected function modulePath(): string
    {
        $reflector = new ReflectionClass($this::class);

        return dirname($reflector->getFileName()).($path ?? '').'/../..';
    }

    protected function registerModuleConfig(): void
    {
        $name = $this->moduleName();

        $this->mergeConfigFrom(
            $this->modulePath()."/config/{$name}.php",
            $name
        );

        $this->registerDatabaseConnection();
    }

    protected function bootModule(): void
    {
        $name = $this->moduleName();
        $path = $this->modulePath();

        // Load migrations
        if (is_dir($path.'/database/migrations')) {
            $this->loadMigrationsFrom($path.'/database/migrations');
        }

        // Load views
        if (is_dir($path.'/resources/views')) {
            $this->loadViewsFrom($path.'/resources/views', $name);
        }

        // Load routes
        if (file_exists($path.'/routes/web.php')) {
            $this->loadRoutesFrom($path.'/routes/web.php');
        }

        // Publish config
        $this->publishes([
            $path."/config/{$name}.php" => config_path("{$name}.php"),
        ], "{$name}-config");

        // Publish database config
        $this->publishes([
            $path.'/config/database.php' => config_path("{$name}-database.php"),
        ], "{$name}-database-config");

        // Publish migrations
        $this->publishes([
            $path.'/database/migrations' => database_path('migrations'),
        ], "{$name}-migrations");

        // Publish views
        $this->publishes([
            $path.'/resources/views' => resource_path("views/vendor/{$name}"),
        ], "{$name}");

        $this->publishes([
            $path.'/resources/assets' => public_path("vendor/{$name}"),
        ], "{$name}-assets");
    }

    protected function registerDatabaseConnection(): void
    {
        $connections = require $this->modulePath().'/config/database.php';

        foreach ($connections['connections'] ?? [] as $name => $config) {
            config(['database.connections.'.$name => $config]);
        }
    }
}
