<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Groupe;
use AcMarche\Conseil\Models\PieceJointe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PieceJointe>
 */
final class PieceJointeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'groupe_id' => Groupe::factory(),
            'nom' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
