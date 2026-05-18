<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Database\Factories;

use AcMarche\StreetWatch\Models\TypeIncident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeIncident>
 */
final class TypeIncidentFactory extends Factory
{
    protected $model = TypeIncident::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
