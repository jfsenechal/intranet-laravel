<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Valorization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Valorization>
 */
final class ValorizationFactory extends Factory
{
    #[Override]
    protected $model = Valorization::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'employer_name' => fake()->company(),
            'duration' => fake()->numberBetween(1, 10).' ans',
            'regime' => 'temps plein',
            'content' => fake()->paragraph(),
        ];
    }
}
