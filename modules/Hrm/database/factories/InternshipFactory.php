<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Internship;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Internship>
 */
final class InternshipFactory extends Factory
{
    #[Override]
    protected $model = Internship::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 year'),
            'notes' => fake()->paragraph(),
            'user_add' => 'tester',
        ];
    }
}
