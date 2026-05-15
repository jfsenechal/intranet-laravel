<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Database\Factories;

use AcMarche\GuichetHdv\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
final class OfficeFactory extends Factory
{
    protected $model = Office::class;

    public function definition(): array
    {
        return [
            'name' => 'Guichet '.$this->faker->numberBetween(1, 10),
            'service' => $this->faker->randomElement(['Population', 'État civil', 'Étrangers']),
        ];
    }
}
