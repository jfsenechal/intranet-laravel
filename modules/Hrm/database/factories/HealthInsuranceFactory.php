<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\HealthInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<HealthInsurance>
 */
final class HealthInsuranceFactory extends Factory
{
    #[Override]
    protected $model = HealthInsurance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
        ];
    }
}
