<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var array<int, string|null> */
    protected array $connectionsToTransact = [null];

    private const MODULE_CONNECTIONS = [
        'mariadb',
        'maria-mailing-list',
        'maria-pst',
        'maria-document',
        'maria-courrier',
        'maria-news',
        'maria-hrm',
        'maria-mileage',
        'maria-publication',
        'maria-guichet',
    ];

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $sqliteConfig = [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', $sqliteConfig);

        foreach (self::MODULE_CONNECTIONS as $name) {
            $this->app['config']->set("database.connections.{$name}", $sqliteConfig);
        }

        $db = $this->app['db'];

        foreach (self::MODULE_CONNECTIONS as $name) {
            $db->purge($name);
        }

        // On subsequent runs, restore the cached PDO (key is null = default connection)
        if (isset(RefreshDatabaseState::$inMemoryConnections[null])) {
            $pdo = RefreshDatabaseState::$inMemoryConnections[null];
            $db->connection('sqlite')->setPdo($pdo)->setReadPdo($pdo);
        }

        // Share the same in-memory PDO across all connections so cross-connection
        // table references work (e.g., security migration altering users table)
        $pdo = $db->connection('sqlite')->getPdo();

        foreach (self::MODULE_CONNECTIONS as $name) {
            $db->connection($name)->setPdo($pdo)->setReadPdo($pdo);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('scout.driver', 'null');

        $this->actingAs(User::factory()->create([
            'name' => config('app.default_user.name'),
            'email' => config('app.default_user.email'),
            'password' => config('app.default_user.password'),
        ]));

        $this->withoutVite();
    }
}
