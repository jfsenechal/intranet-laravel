<?php

declare(strict_types=1);

namespace AcMarche\Publication\Database\Factories;

use AcMarche\Publication\Models\Category;
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
            'name' => fake()->word(),
            'url' => fake()->url(),
            'wpCategoryId' => fake()->numberBetween(1, 100),
        ];
    }
}
