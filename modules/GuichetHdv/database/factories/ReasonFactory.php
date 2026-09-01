<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Database\Factories;

use AcMarche\GuichetHdv\Enums\ServicesEnum;
use AcMarche\GuichetHdv\Models\Reason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reason>
 */
final class ReasonFactory extends Factory
{
    protected $model = Reason::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(4),
            'service' => $this->faker->randomElement(ServicesEnum::cases()),
        ];
    }
}
