<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Application;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Application>
 */
final class ApplicationFactory extends Factory
{
    #[Override]
    protected $model = Application::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'received_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->paragraph(),
            'is_spontaneous' => fake()->boolean(),
            'is_public_call' => fake()->boolean(),
            'is_priority' => fake()->boolean(),
        ];
    }
}
