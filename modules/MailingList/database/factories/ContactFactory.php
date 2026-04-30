<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Factories;

use AcMarche\MailingList\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
final class ContactFactory extends Factory
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
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'description' => fake()->optional()->sentence(),
            'phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
