<?php

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Database\Eloquent\Factories\Factory;

final class IncomingMailFactory extends Factory
{
    protected $model = IncomingMail::class;

    public function definition(): array
    {
        return [
            'reference_number' => fake()->unique()->numerify('######'),
            'sender' => fake()->company(),
            'description' => fake()->optional()->paragraph(),
            'mail_date' => fake()->date(),
            'is_notified' => fake()->boolean(70),
            'is_registered' => fake()->boolean(20),
            'has_acknowledgment' => fake()->boolean(10),
            'user_add' => 'test_user',
            'department' => null,
        ];
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_notified' => true,
        ]);
    }

    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registered' => true,
        ]);
    }

    public function withAcknowledgment(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_acknowledgment' => true,
        ]);
    }
}
