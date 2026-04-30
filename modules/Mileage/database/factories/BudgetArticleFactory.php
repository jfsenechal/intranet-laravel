<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Database\Factories;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<BudgetArticle>
 */
final class BudgetArticleFactory extends Factory
{
    #[Override]
    protected $model = BudgetArticle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->numerify('BUDGET-####'),
            'functional_code' => fake()->numerify('###/###'),
            'economic_code' => fake()->numerify('######'),
            'department' => fake()->randomElement(DepartmentEnum::cases())->value,
        ];
    }
}
