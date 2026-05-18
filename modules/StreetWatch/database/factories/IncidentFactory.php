<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Database\Factories;

use AcMarche\StreetWatch\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
final class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place' => fake()->streetName(),
            'object' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'response' => null,
            'user_add' => fake()->userName(),
            'occurred_date' => fake()->dateTimeThisYear(),
            'requestBy_id' => RequestByFactory::new(),
            'typeIncident_id' => TypeIncidentFactory::new(),
            'createdAt' => now(),
            'updatedAt' => now(),
        ];
    }
}
