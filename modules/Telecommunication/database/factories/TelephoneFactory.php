<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Database\Factories;

use AcMarche\Telecommunication\Models\LineType;
use AcMarche\Telecommunication\Models\Telephone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Telephone>
 */
final class TelephoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userName = fake()->name();

        return [
            'line_type_id' => LineType::factory(),
            'slug' => Str::slug($userName.'-'.fake()->unique()->randomNumber(5)),
            'user_name' => $userName,
            'number' => fake()->phoneNumber(),
            'archived' => false,
            'mobistar' => null,
            'proximus' => null,
            'service' => fake()->optional()->word(),
            'department' => fake()->optional()->word(),
            'budget_article' => fake()->optional()->bothify('###/###/###'),
            'location' => fake()->optional()->city(),
            'fixed_cost' => fake()->optional()->randomFloat(2, 0, 100),
            'note' => fake()->optional()->paragraph(),
        ];
    }
}
