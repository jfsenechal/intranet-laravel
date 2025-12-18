<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Database\Factories;

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\AcMarche\Mileage\Models\Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'declaration_id' => Declaration::factory(),
            'user_id' => User::factory(),
            'distance' => fake()->numberBetween(1, 500),
            'departure_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'arrival_date' => fake()->dateTimeBetween('now', '+1 month'),
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
            'content' => fake()->sentence(),
            'rate' => fake()->randomFloat(2, 0.30, 0.50),
            'omnium' => fake()->randomFloat(2, 0, 10),
            'user_add' => fake()->userName(),
            'type_movement' => 'service',
            'departure_location' => fake()->city(),
            'arrival_location' => fake()->city(),
            'meal_expense' => fake()->randomFloat(2, 0, 50),
            'train_expense' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
