<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Pv;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pv>
 */
final class PvFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->sentence(4),
            'date_pv' => fake()->dateTimeBetween('-2 years', 'now'),
            'file_name' => fake()->uuid().'.pdf',
        ];
    }
}
