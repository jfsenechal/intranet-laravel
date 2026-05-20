<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
final class ActivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'user' => fake()->userName(),
            'archived' => false,
        ];
    }
}
