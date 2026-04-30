<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Recipient>
 */
final class RecipientFactory extends Factory
{
    #[Override]
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
            'receives_attachments' => fake()->boolean(30),
        ];
    }

    public function withSupervisor(?Recipient $supervisor = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'supervisor_id' => $supervisor?->id ?? Recipient::factory(),
        ]);
    }

    public function receivesAttachments(): static
    {
        return $this->state(fn (array $attributes): array => [
            'receives_attachments' => true,
        ]);
    }
}
