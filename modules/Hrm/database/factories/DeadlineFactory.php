<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Deadline>
 */
final class DeadlineFactory extends Factory
{
    #[Override]
    protected $model = Deadline::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'name' => fake()->sentence(3),
            'note' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 month'),
            'is_closed' => false,
            'user_add' => 'tester',
        ];
    }
}
