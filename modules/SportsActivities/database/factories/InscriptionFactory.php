<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activite;
use AcMarche\SportsActivities\Models\Groupe;
use AcMarche\SportsActivities\Models\Inscription;
use AcMarche\SportsActivities\Models\Sportif;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inscription>
 */
final class InscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activite = Activite::factory()->create();
        $groupe = Groupe::factory()->create(['activite_id' => $activite->id]);

        return [
            'activite_id' => $activite->id,
            'groupe_id' => $groupe->id,
            'sportif_id' => Sportif::factory(),
            'prix' => fake()->optional()->randomFloat(2, 0, 200),
            'remarque' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
