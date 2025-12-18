<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Database\Factories;

use AcMarche\Mileage\Models\Declaration;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeclarationFactory extends Factory
{
    protected $model = Declaration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'omnium' => fake()->boolean(),
            'iban' => fake()->iban('BE'),
            'car_license_plate1' => fake()->bothify('?-???-###'),
            'car_license_plate2' => null,
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'rate' => fake()->randomFloat(2, 0.30, 0.50),
            'rate_omnium' => fake()->randomFloat(2, 0.01, 0.05),
            'user_add' => fake()->userName(),
            'type_movement' => 'service',
            'college_date' => fake()->date(),
            'budget_article' => fake()->jobTitle(),
            'departments' => 'ville',
        ];
    }
}
