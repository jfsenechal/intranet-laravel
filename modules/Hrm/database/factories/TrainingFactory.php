<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Training>
 */
final class TrainingFactory extends Factory
{
    #[Override]
    protected $model = Training::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 year'),
            'duration_minutes' => 60,
            'training_type' => TrainingTypeEnum::TYPE1->value,
            'certificate_received' => false,
            'is_closed' => false,
            'user_add' => 'tester',
        ];
    }
}
