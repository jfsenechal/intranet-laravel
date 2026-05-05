<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Enums\EvaluationResultEnum;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Evaluation>
 */
final class EvaluationFactory extends Factory
{
    #[Override]
    protected $model = Evaluation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'evaluation_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'next_evaluation_date' => fake()->dateTimeBetween('now', '+1 year'),
            'notes' => fake()->paragraph(),
            'result' => EvaluationResultEnum::POSITIVE->value,
            'user_add' => 'tester',
        ];
    }
}
