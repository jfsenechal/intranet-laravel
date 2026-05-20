<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
final class ActiviteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
