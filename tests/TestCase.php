<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    /**
     * Connections RefreshDatabase should transact.
     *
     * Composed in {@see self::refreshApplication()} from the default connection,
     * {@see self::DEDICATED_CONNECTIONS} and {@see self::$extraConnectionsToTransact}.
     * RefreshDatabase reads this property rather than a method, because the
     * LazilyRefreshDatabase trait redeclares connectionsToTransact() in every
     * generated Pest test class and so would win over a parent override.
     *
     * @var array<int, string|null>
     */
    protected array $connectionsToTransact = [null];

    /**
     * Extra connections this test case needs transacted, on top of the default
     * connection and {@see self::DEDICATED_CONNECTIONS}.
     *
     * @var array<int, string>
     */
    protected array $extraConnectionsToTransact = [];

    /**
     * Connections that get their own in-memory PDO instead of sharing the default one.
     *
     * These modules' migrations guard on hasTable()/hasColumn(), so they must run
     * against a schema holding only their own tables.
     *
     * This mapping lives here rather than in a per-module TestCase because
     * RefreshDatabaseState is process-wide static state and migrations run only
     * once per process. A test case that left one of these on the shared PDO would
     * look for tables that were migrated into the dedicated one, so whichever
     * suite happened to run first decided whether the other could see its tables.
     */
    private const DEDICATED_CONNECTIONS = [
        'maria-hrm',
        'maria-meal-delivery',
        'maria-email-management',
    ];

    private const MODULE_CONNECTIONS = [
        'mariadb',
        'maria-mailing-list',
        'maria-pst',
        'maria-document',
        'maria-courrier',
        'maria-news',
        'maria-note',
        'maria-hrm',
        'maria-mileage',
        'maria-publication',
        'maria-guichet',
        'maria-cpas-library',
        'maria-college',
        'maria-activity-manager',
        'maria-street-watch',
        'maria-ad',
        'maria-rescam',
        'maria-meal-delivery',
        'maria-agent',
        'maria-aldermen-agenda',
        'maria-conseil',
        'maria-mediation',
        'maria-offenses',
        'maria-telecommunication',
        'maria-email-management',
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
            if (in_array($name, self::DEDICATED_CONNECTIONS, true)) {
                continue;
            }

            $db->connection($name)->setPdo($pdo)->setReadPdo($pdo);
        }

        foreach (self::DEDICATED_CONNECTIONS as $name) {
            $this->dedicateInMemoryPdo($name);
        }

        // Assigned here rather than in the property initializer so subclasses only
        // declare their extra connections. This runs before setUpTraits() boots
        // RefreshDatabase, which is what reads the composed list.
        $this->connectionsToTransact = array_merge(
            [null],
            self::DEDICATED_CONNECTIONS,
            $this->extraConnectionsToTransact,
        );
    }

    /**
     * Point a connection at its own in-memory PDO, reusing the one cached by an
     * earlier test so the schema survives between the migration run and each test
     * that follows it.
     *
     * The handle is deliberately not PDO::ATTR_PERSISTENT: every in-memory sqlite
     * DSN is identical, so persistent handles are pooled by DSN and two dedicated
     * connections would be handed the same one. They would then share a schema and
     * fail to open a transaction each ("cannot start a transaction within a
     * transaction"). Caching in RefreshDatabaseState is what keeps the handle
     * alive, which is also how the framework itself holds in-memory databases.
     */
    protected function dedicateInMemoryPdo(string $name): void
    {
        $pdo = RefreshDatabaseState::$inMemoryConnections[$name] ??= new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->app['db']->connection($name)->setPdo($pdo)->setReadPdo($pdo);
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
