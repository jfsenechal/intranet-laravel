<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {

        parent::setUp();

        $this->actingAs(User::factory()->create([
            'name' => config('app.default_user.name'),
            'email' => config('app.default_user.email'),
            'password' => config('app.default_user.password'),
        ]));

        $this->withoutVite();
    }

    protected $connectionsToTransact = [
        'mariadb',
        'maria-courrier',
        'maria-document',
        'maria-mileage',
        'maria-news',
        'maria-publication',
        'maria-security',
        'maria-hrm',
    ];

    protected function beforeRefreshingDatabase(): void
    {
        $path = storage_path().'/../data/sqlite/';
        $tables = ['courrier', 'document', 'intranet', 'mileage', 'news', 'publication', 'hrm'];

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
