<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Diploma>
 */
final class DiplomaFactory extends Factory
{
    #[Override]
    protected $model = Diploma::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'name' => fake()->sentence(3),
            'user_add' => 'tester',
        ];
    }
}
