<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Sportif;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sportif>
 */
final class SportifFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'ne_le' => fake()->optional()->dateTimeBetween('-80 years', '-6 years'),
            'rue' => fake()->streetAddress(),
            'code_postal' => fake()->postcode(),
            'localite' => fake()->city(),
            'telephone' => fake()->optional()->phoneNumber(),
            'gsm' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'remarque' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
