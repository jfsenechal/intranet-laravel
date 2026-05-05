<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Prerequisite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Prerequisite>
 */
final class PrerequisiteFactory extends Factory
{
    #[Override]
    protected $model = Prerequisite::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'profession' => fake()->jobTitle(),
            'description' => fake()->sentence(),
            'user' => 'tester',
            'employer_id' => null,
        ];
    }
}
