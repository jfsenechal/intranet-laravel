<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Factories;

use AcMarche\MailingList\Models\AddressBook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AddressBook>
 */
final class AddressBookFactory extends Factory
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
            'name' => fake()->words(2, true),
            'is_public' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'is_public' => true,
        ]);
    }
}
