<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<ContractType>
 */
final class ContractTypeFactory extends Factory
{
    #[Override]
    protected $model = ContractType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'employer_id' => null,
        ];
    }
}
