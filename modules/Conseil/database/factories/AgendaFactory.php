<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agenda>
 */
final class AgendaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'agenda_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'distribution_end_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'file_name' => fake()->uuid().'.pdf',
        ];
    }
}
