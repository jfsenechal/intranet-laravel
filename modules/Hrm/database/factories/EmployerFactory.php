<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Employer>
 */
final class EmployerFactory extends Factory
{
    #[Override]
    protected $model = Employer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null,
        ];
    }
}
