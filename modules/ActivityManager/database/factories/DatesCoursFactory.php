<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Models\Cours;
use AcMarche\ActivityManager\Models\DatesCours;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatesCours>
 */
final class DatesCoursFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cours_id' => Cours::factory(),
            'remarque' => fake()->optional()->sentence(),
            'jour' => fake()->dateTimeBetween('-1 year', '+1 year'),
        ];
    }
}
