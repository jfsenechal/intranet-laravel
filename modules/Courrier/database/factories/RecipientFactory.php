<?php

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class RecipientFactory extends Factory
{
    protected $model = Recipient::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'supervisor_id' => null,
            'slug' => Str::slug($lastName.'_'.$firstName),
            'last_name' => $lastName,
            'first_name' => $firstName,
            'username' => Str::lower($firstName[0].$lastName),
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
            'receives_attachments' => fake()->boolean(30),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withSupervisor(?Recipient $supervisor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'supervisor_id' => $supervisor?->id ?? Recipient::factory(),
        ]);
    }

    public function receivesAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'receives_attachments' => true,
        ]);
    }
}
