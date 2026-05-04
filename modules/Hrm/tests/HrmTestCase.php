<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PDO;
use Tests\TestCase;

abstract class HrmTestCase extends TestCase
{
    /** @var array<int, string|null> */
    protected array $connectionsToTransact = [null, 'maria-hrm'];

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $db = $this->app['db'];

        // Give maria-hrm its own PDO so HRM migrations create their own schema
        // instead of seeing tables created by other modules on the shared PDO.
        $hrmPdo = RefreshDatabaseState::$inMemoryConnections['maria-hrm']
            ?? new PDO('sqlite::memory:', null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]);

        RefreshDatabaseState::$inMemoryConnections['maria-hrm'] = $hrmPdo;
        $db->connection('maria-hrm')->setPdo($hrmPdo)->setReadPdo($hrmPdo);
    }
}
