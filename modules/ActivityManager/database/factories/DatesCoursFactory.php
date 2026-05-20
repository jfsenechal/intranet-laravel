<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\ActivityManager\Models\SchedulesActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchedulesActivity>
 */
final class DatesCoursFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'comment' => fake()->optional()->sentence(),
            'schedule_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
        ];
    }
}
