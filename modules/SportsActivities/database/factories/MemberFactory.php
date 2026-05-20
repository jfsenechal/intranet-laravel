<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
final class MemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'birth_date' => fake()->optional()->dateTimeBetween('-80 years', '-6 years'),
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'phone' => fake()->optional()->phoneNumber(),
            'mobile' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'comment' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
