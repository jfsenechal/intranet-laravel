<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Database\Factories;

use AcMarche\GuichetHdv\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'number' => (string) $this->faker->numberBetween(1, 999),
            'reason' => $this->faker->randomElement([
                'Carte d\'identité (DEMANDE/RETRAIT)',
                'Passeport (DEMANDE ou RETRAIT)',
                'Permis de conduire définitif (DEMANDE)',
                'Certificat de vie',
                'Cohabitation légale (DECLARATION)',
            ]),
            'service' => $this->faker->randomElement(['Population', 'État civil', 'Étrangers']),
            'assigned_date' => null,
            'assigned_by' => null,
            'user_add' => $this->faker->userName(),
            'archive' => false,
            'office_id' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_date' => now(),
            'assigned_by' => $this->faker->userName(),
            'archive' => true,
        ]);
    }
}
