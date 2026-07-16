<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PDO;
use Tests\TestCase;

/**
 * Keeps EmailManagement tests off the real gestemail database and off the live mail server.
 *
 * phpunit.xml only overrides the default connection, so without this the
 * maria-email-management connection resolves to the live MariaDB from .env and
 * factories write real rows that no rollback removes.
 *
 * Follows AcMarche\Hrm\Tests\HrmTestCase, but also rewrites the connection's driver
 * to sqlite. Swapping the PDO alone would leave the mariadb query grammar in place,
 * and this module's migrations guard on hasTable()/hasColumn(), which that grammar
 * answers with an information_schema lookup sqlite cannot serve.
 */
abstract class EmailManagementTestCase extends TestCase
{
    /** @var array<int, string|null> */
    protected array $connectionsToTransact = [null, 'maria-email-management'];

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        // The IMAP and Sieve credentials in .env point at the production mail server, and
        // ImapEmploye/SieveEmploye reach for it as soon as they are configured. Blanking
        // them here makes both report themselves unconfigured, so a test renders the quota
        // as unavailable instead of opening a socket to mail.marche.be. Tests that need
        // these services bind their own doubles.
        $this->app['config']->set('email-management.imap', null);
        $this->app['config']->set('email-management.sieve', null);

        $this->app['config']->set('database.connections.maria-email-management', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $db = $this->app['db'];
        $db->purge('maria-email-management');

        // Give the connection its own persistent PDO so this module's migrations create
        // their own schema, and so it survives between the migration run and the test.
        $pdo = RefreshDatabaseState::$inMemoryConnections['maria-email-management']
            ?? new PDO('sqlite::memory:', null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]);

        RefreshDatabaseState::$inMemoryConnections['maria-email-management'] = $pdo;
        $db->connection('maria-email-management')->setPdo($pdo)->setReadPdo($pdo);
    }
}
