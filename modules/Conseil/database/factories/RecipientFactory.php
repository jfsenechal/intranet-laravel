<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipient>
 */
final class RecipientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
