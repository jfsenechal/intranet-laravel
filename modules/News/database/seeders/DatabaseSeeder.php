<?php

namespace AcMarche\News\Database\Seeders;

use AcMarche\News\Models\News;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        News::factory(5)->create([]);
    }
}
