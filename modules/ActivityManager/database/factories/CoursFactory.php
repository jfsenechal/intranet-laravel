<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Models\Activite;
use AcMarche\ActivityManager\Models\Cours;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cours>
 */
final class CoursFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateDebut = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'nom' => fake()->sentence(6),
            'date_debut' => $dateDebut,
            'date_fin' => fake()->dateTimeBetween($dateDebut, '+1 year'),
            'activite_id' => Activite::factory(),
        ];
    }
}
