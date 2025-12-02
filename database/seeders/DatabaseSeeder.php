<?php

namespace Database\Seeders;

use AcMarche\Security\Database\Seeders\DatabaseSeeder as SecurityDatabaseSeeder;
use AcMarche\Security\Models\Tab;
use AcMarche\Security\Repository\ModuleRepository;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            SecurityDatabaseSeeder::class,
        ]);

        $repository = new ModuleRepository();
        foreach ($repository->getModulesWithoutTab() as $module) {
            $tab = $this->createTab($module->id);
            if ($tab) {
                $module->tab_id = $tab->id;
                $module->save();
            }
        }
    }

    private function createTab(int $moduleId): ?Tab
    {
        return match ($moduleId) {
            // Indicateurs
            1, 2, 16 => Tab::firstOrCreate(['name' => 'Indicateurs']),

            // Administration & Gouvernance
            3, 11, 12, 17, 19, 25, 36, 40 => Tab::firstOrCreate(['name' => 'Administration']),

            // Ressources Humaines
            6, 9, 13, 21, 26, 29, 50 => Tab::firstOrCreate(['name' => 'Ressources Humaines']),

            // Communication & Information
            5, 10, 15, 28, 31, 33, 44 => Tab::firstOrCreate(['name' => 'Communication']),

            // Services aux Citoyens
            4, 14, 18, 59, 60 => Tab::firstOrCreate(['name' => 'Services aux Citoyens']),

            // Sport & Activités
            20, 30, 41, 54, 57 => Tab::firstOrCreate(['name' => 'Sport & Activités']),

            // Outils Numériques & Données
            22, 23, 32, 42, 45, 46, 47, 48, 49, 56 => Tab::firstOrCreate(['name' => 'Outils Numériques']),

            // Social & CPAS
            39, 52, 55, 58 => Tab::firstOrCreate(['name' => 'Social & CPAS']),

            default => null,
        };
    }
}
