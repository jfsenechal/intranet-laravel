<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Database\Factories;

use AcMarche\Mileage\Models\Rate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Rate>
 */
final class RateFactory extends Factory
{
    #[Override]
    protected $model = Rate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 0.30, 0.50),
            'omnium' => fake()->randomFloat(2, 0.01, 0.05),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
