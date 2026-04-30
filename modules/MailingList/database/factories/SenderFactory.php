<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Factories;

use AcMarche\MailingList\Models\Sender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sender>
 */
final class SenderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'name' => fake()->company(),
            'email' => fake()->unique()->userName().'@marche.be',
        ];
    }
}
