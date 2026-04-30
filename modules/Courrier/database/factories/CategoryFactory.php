<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    #[Override]
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->hexColor(),
        ];
    }
}
