<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Database\Factories;

use AcMarche\Mileage\Models\PersonalInformation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\AcMarche\Mileage\Models\PersonalInformation>
 */
final class PersonalInformationFactory extends Factory
{
    protected $model = PersonalInformation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_license_plate1' => fake()->bothify('?-???-###'),
            'car_license_plate2' => null,
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'username' => fake()->unique()->userName(),
            'college_trip_date' => fake()->date(),
            'iban' => fake()->iban('BE'),
            'omnium' => fake()->boolean(),
        ];
    }
}
