<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Database\Factories;

use AcMarche\SportsActivities\Models\Activity;
use AcMarche\SportsActivities\Models\Group;
use AcMarche\SportsActivities\Models\Member;
use AcMarche\SportsActivities\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
final class RegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activity = Activity::factory()->create();
        $group = Group::factory()->create(['activity_id' => $activity->id]);

        return [
            'activity_id' => $activity->id,
            'group_id' => $group->id,
            'member_id' => Member::factory(),
            'price' => fake()->optional()->randomFloat(2, 0, 200),
            'comment' => fake()->optional()->sentence(),
            'user' => fake()->userName(),
        ];
    }
}
