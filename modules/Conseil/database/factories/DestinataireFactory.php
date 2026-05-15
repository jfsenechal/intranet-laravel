<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Destinataire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Destinataire>
 */
final class DestinataireFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
