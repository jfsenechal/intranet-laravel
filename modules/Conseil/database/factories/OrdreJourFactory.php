<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\OrdreJour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdreJour>
 */
final class OrdreJourFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->sentence(4),
            'date_ordre' => fake()->dateTimeBetween('-2 years', 'now'),
            'date_fin_diffusion' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'file_name' => fake()->uuid().'.pdf',
        ];
    }
}
