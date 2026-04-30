<?php

declare(strict_types=1);

namespace AcMarche\Pst\Database\Seeders;

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Models\Odd;
use AcMarche\Pst\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    private static ?string $password;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::factory()->create([
            'name' => RoleEnum::ADMIN->value,
        ]);

        foreach (RoleEnum::cases() as $role) {
            if ($role !== RoleEnum::ADMIN) {
                Role::factory()->create([
                    'name' => $role->value,
                    'description' => $role->getDescription(),
                ]);
            }
        }

        User::factory()
            ->hasAttached($adminRole)
            ->create([
                'first_name' => config('app.default_user.name'),
                'last_name' => 'Sénéchal',
                'email' => config('app.default_user.email'),
                'username' => config('app.default_user.username'),
                'password' => self::$password ??= Hash::make(config('app.default_user.password')),
            ]);

        foreach ($this->services() as $service) {
            Service::Factory()->create([
                'name' => $service,
            ]);
        }
        foreach ($this->odds() as $odd) {
            Odd::Factory()->create([
                'name' => $odd,
            ]);
        }
    }

    public function services(): array
    {
        return [
            'ADT',
            'ADT/Bien-être animal',
            'ADT/Environnement',
            'ADT/Grandes infrastructures',
            'ADT/Logement',
            'ADT/Mobilité',
            'ADT/Santé',
            'Agence de Développement Local (ADL)',
            'Cabinet du Bourgmestre',
            'CEE/Enfance',
            'CEE/Enseignement',
            'CEE/Petite Enfance',
            'Cellule Marchés publics',
            'Cellule Transition',
            'CODIR CPAS',
            'CODIR commun',
            'CODIR Ville',
            'Communication',
            'CPAS',
            'CPAS/MRS',
            'Direction générale',
            'Direction financière',
            'Eco Team',
            'e-Square',
            'GRH',
            'JCS/Animation',
            'JCS/Culture',
            'JCS/Jeunesse',
            'JCS/OCT',
            'JCS/Sport/RESCAM',
            'Juridique',
            'Plan de Cohésion Sociale (PCS)',
            "Planification d'urgence (PLANU)",
            'Plan Stratégique de Sécurité et de Prévention (PSSP)',
            'Patrimoine',
            'Population/Etat civil/Etrangers',
            'Prévention (SIPPT)',
            'Tous services',
            'Travaux',
            'Travaux/Energie',
        ];
    }

    public function odds(): array
    {
        return [
            'Pas de pauvreté',
            'Faim Zéro',
            'Bonne santé et bien-être',
            'Éducation de qualité',
            'Égalité entre les sexes',
            'Eau propre et assainissement',
            'Énergie propre et d’un coût abordable',
            '-Travail décent et croissance économique',
            'Industrie, innovation et infrastructure',
            'Inégalités réduites',
            'Villes et communautés durables',
            'Consommation et production responsables',
            'Mesures relatives à la lutte contre les changements climatiques',
            'Vie aquatique',
            'Vie terrestre',
            'Paix, justice et institutions efficaces',
            'Partenariats pour la réalisation des objectifs',
        ];
    }
}
