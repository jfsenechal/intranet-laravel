<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Contact>
 */
final class ContactFactory extends Factory
{
    #[Override]
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'email_1' => fake()->unique()->safeEmail(),
            'phone_1' => fake()->phoneNumber(),
            'email_2' => fake()->unique()->safeEmail(),
            'phone_2' => fake()->phoneNumber(),
            'description' => fake()->sentence(),
        ];
    }
}
