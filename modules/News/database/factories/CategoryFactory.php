<?php

declare(strict_types=1);

namespace AcMarche\News\Database\Factories;

use AcMarche\News\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    #[Override]
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'icon' => 'heroicon-o-tag',
        ];
    }
}
