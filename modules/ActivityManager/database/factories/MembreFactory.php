<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Database\Factories;

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use AcMarche\ActivityManager\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
final class MembreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civility' => fake()->randomElement(CiviliteEnum::cases())->value,
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'postal_code' => 6900,
            'city' => 'Marche-en-Famenne',
            'mobile' => fake()->numerify('04## ## ## ##'),
            'phone' => fake()->numerify('084 ## ## ##'),
            'email' => fake()->unique()->safeEmail(),
            'enabled' => true,
            'remark' => fake()->optional()->sentence(),
            'registered_at' => fake()->dateTimeBetween('-5 years', 'now'),
        ];
    }
}
