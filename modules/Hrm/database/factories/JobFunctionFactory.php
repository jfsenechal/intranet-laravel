<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\JobFunction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<JobFunction>
 */
final class JobFunctionFactory extends Factory
{
    #[Override]
    protected $model = JobFunction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
        ];
    }
}
