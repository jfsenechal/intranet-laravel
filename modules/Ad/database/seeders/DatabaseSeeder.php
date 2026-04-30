<?php

declare(strict_types=1);

namespace AcMarche\Ad\Database\Seeders;

use AcMarche\Ad\Models\ClassifiedAd;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ClassifiedAd::factory(5)->create([]);
    }
}
