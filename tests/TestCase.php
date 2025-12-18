<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = [
        'mariadb',
        'maria-courrier',
        'maria-document',
        'maria-mileage',
        'maria-news',
        'maria-publication',
        'maria-security',
    ];

    protected function beforeRefreshingDatabase(): void
    {
        $path = storage_path().'/../data/sqlite/';
        $tables = ['courrier', 'document', 'intranet', 'mileage', 'news', 'publication'];

        if (is_writable($path)) {
            shell_exec('rm -f '.$path.'*');
            foreach ($tables as $table) {
                shell_exec('touch '.$path.$table);
            }
        }
        $this->artisan('migrate --env testing');
        RefreshDatabaseState::$migrated = true;
    }

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        //do nothing
    }

}
