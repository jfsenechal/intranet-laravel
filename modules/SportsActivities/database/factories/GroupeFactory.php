<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activite;
use AcMarche\SportsActivities\Models\Groupe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Groupe>
 */
final class GroupeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activite_id' => Activite::factory(),
            'jour' => fake()->randomElement(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']),
            'heure' => fake()->time('H:i'),
            'lieux' => fake()->city(),
            'age' => fake()->randomElement(['6-10 ans', '11-14 ans', '15+ ans', 'Adultes', 'Tous âges']),
            'prix' => fake()->randomFloat(2, 0, 200),
            'description' => fake()->optional()->paragraph(),
            'remarque' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
