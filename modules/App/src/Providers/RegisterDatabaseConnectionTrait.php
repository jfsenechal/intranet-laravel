<?php

namespace AcMarche\App\Providers;

trait RegisterDatabaseConnectionTrait
{
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
