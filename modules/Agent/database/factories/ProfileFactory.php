<?php

declare(strict_types=1);

namespace AcMarche\Agent\Database\Factories;

use AcMarche\Agent\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Profile>
 */
final class ProfileFactory extends Factory
{
    #[Override]
    protected $model = Profile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastName = fake()->lastName();
        $firstName = fake()->firstName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => fake()->unique()->userName(),
            'emails' => [fake()->safeEmail()],
            'supervisors' => [],
            'location' => fake()->city(),
            'notes' => null,
            'modules' => [],
            'employee_id' => null,
            'uuid' => (string) Str::uuid(),
            'no_mail' => false,
        ];
    }
}
