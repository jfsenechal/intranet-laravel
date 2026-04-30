<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Database\Seeders;

use AcMarche\QrCode\Models\QrCode;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        QrCode::factory(5)->create();
    }
}
