<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activite>
 */
final class ActiviteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'user' => fake()->userName(),
            'archive' => false,
        ];
    }
}
