<?php

declare(strict_types=1);

namespace Database\Factories;

use AcMarche\Mileage\Models\BudgetArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetArticleFactory extends Factory
{
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
            'department' => fake()->randomElement(['ville', 'cpas']),
        ];
    }
}
