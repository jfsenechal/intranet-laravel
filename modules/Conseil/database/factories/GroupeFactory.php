<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Groupe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Groupe>
 */
final class GroupeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->words(3, true),
        ];
    }
}
