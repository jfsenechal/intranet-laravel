<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Enums\ReasonsEnum;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Absence>
 */
final class AbsenceFactory extends Factory
{
    #[Override]
    protected $model = Absence::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 month'),
            'notes' => fake()->paragraph(),
            'reason' => ReasonsEnum::cases()[0]->value,
            'is_closed' => false,
            'user_add' => 'tester',
        ];
    }
}
