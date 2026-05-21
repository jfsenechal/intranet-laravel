<?php

declare(strict_types=1);

namespace AcMarche\College\Database\Factories;

use AcMarche\College\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'slugname' => Str::slug($lastName.'_'.$firstName, '_').'_'.fake()->unique()->numberBetween(1, 99999),
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
