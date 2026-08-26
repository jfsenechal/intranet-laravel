<?php

declare(strict_types=1);

namespace AcMarche\College\Database\Factories;

use AcMarche\College\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipient>
 */
final class RecipientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastName = fake()->lastName();
        $firstName = fake()->firstName();

        return [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => fake()->unique()->safeEmail(),
            'pv_service' => fake()->boolean(),
            'ordre_service' => fake()->boolean(),
            'ordre_college' => fake()->boolean(),
            'pv_college' => fake()->boolean(),
        ];
    }
}
