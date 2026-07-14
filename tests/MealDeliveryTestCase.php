<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PDO;

abstract class MealDeliveryTestCase extends TestCase
{
    /** @var array<int, string|null> */
    protected array $connectionsToTransact = [null, 'maria-meal-delivery'];

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $db = $this->app['db'];

        // Give maria-meal-delivery its own PDO so its migrations create their own
        // schema and code that opens an explicit transaction on this connection
        // nests as a savepoint instead of colliding with the shared default PDO.
        $mealDeliveryPdo = RefreshDatabaseState::$inMemoryConnections['maria-meal-delivery']
            ?? new PDO('sqlite::memory:', null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]);

        RefreshDatabaseState::$inMemoryConnections['maria-meal-delivery'] = $mealDeliveryPdo;
        $db->connection('maria-meal-delivery')->setPdo($mealDeliveryPdo)->setReadPdo($mealDeliveryPdo);
    }
}
