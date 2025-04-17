<?php

namespace Database\Seeders;

use AcMarche\Security\Database\Seeders\DatabaseSeeder as SecurityDatabaseSeeder;
use AcMarche\App\Database\Seeders\DatabaseSeeder as AppDatabaseSeeder;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            SecurityDatabaseSeeder::class,
            AppDatabaseSeeder::class,
        ]);
    }
}
