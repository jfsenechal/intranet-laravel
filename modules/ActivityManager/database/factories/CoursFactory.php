<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
final class CoursFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'name' => fake()->sentence(6),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '+1 year'),
            'activity_id' => Activity::factory(),
        ];
    }
}
