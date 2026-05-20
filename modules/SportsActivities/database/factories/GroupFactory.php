<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activity;
use AcMarche\SportsActivities\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
final class GroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'day' => fake()->randomElement(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']),
            'time' => fake()->time('H:i'),
            'location' => fake()->city(),
            'age' => fake()->randomElement(['6-10 ans', '11-14 ans', '15+ ans', 'Adultes', 'Tous âges']),
            'price' => fake()->randomFloat(2, 0, 200),
            'description' => fake()->optional()->paragraph(),
            'comment' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
