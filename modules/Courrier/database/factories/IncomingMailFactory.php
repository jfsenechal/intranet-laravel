<?php

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomingMail>
 */
final class IncomingMailFactory extends Factory
{
    protected $model = IncomingMail::class;

    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->numerify('MAIL-####-####'),
            'sender_name' => fake()->company(),
            'sender_address' => fake()->address(),
            'received_date' => fake()->date(),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'processed', 'archived']),
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_size' => null,
            'attachment_mime' => null,
            'assigned_to' => fake()->optional()->name(),
            'processed_date' => fake()->optional()->date(),
            'notes' => fake()->optional()->paragraph(),
            'user_add' => 'test_user',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_date' => null,
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'processed_date' => fake()->date(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function withAttachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachment_path' => fake()->filePath(),
            'attachment_name' => fake()->word().'.pdf',
            'attachment_size' => fake()->numberBetween(1024, 1024000),
            'attachment_mime' => 'application/pdf',
        ]);
    }
}
