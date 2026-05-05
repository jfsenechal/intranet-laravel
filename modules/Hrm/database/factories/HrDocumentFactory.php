<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\HrDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<HrDocument>
 */
final class HrDocumentFactory extends Factory
{
    #[Override]
    protected $model = HrDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'name' => fake()->sentence(3),
            'file_name' => fake()->word().'.pdf',
            'mime' => 'application/pdf',
            'notes' => fake()->paragraph(),
        ];
    }
}
