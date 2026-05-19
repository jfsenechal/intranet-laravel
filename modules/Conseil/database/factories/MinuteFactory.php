<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Minute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Minute>
 */
final class MinuteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'meeting_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'file_name' => fake()->uuid().'.pdf',
        ];
    }
}
