<?php

namespace AcMarche\Document\Database\Seeders;

use AcMarche\Document\Models\Document;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Document::factory(5)->create([]);
    }
}
