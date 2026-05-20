<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use AcMarche\ActivityManager\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
final class MembreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civilite' => fake()->randomElement(CiviliteEnum::cases())->value,
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'rue' => fake()->streetName(),
            'numero' => fake()->buildingNumber(),
            'codepostal' => 6900,
            'localite' => 'Marche-en-Famenne',
            'gsm' => fake()->numerify('04## ## ## ##'),
            'telephone' => fake()->numerify('084 ## ## ##'),
            'email' => fake()->unique()->safeEmail(),
            'enabled' => true,
            'remarque' => fake()->optional()->sentence(),
            'inscrit_le' => fake()->dateTimeBetween('-5 years', 'now'),
        ];
    }
}
