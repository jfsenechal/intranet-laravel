<?php

declare(strict_types=1);

use AcMarche\Security\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const string MODULE_NAME = 'Application de pointage';

    public function up(): void
    {
        Module::query()->updateOrCreate(
            ['name' => self::MODULE_NAME],
            [
                'description' => 'Accédez à l\'application de pointage et encodez vos heures de travail.',
                'url' => 'https://pointage.marche.be/idtech/unitimeweb',
                'is_external' => true,
                'is_public' => true,
                'icon' => 'fa-solid fa-clock icon-cyan',
                'color' => '#06b6d4',
            ],
        );
    }

    public function down(): void
    {
        Module::query()->where('name', self::MODULE_NAME)->delete();
    }
};
