<?php

declare(strict_types=1);

namespace AcMarche\News\Database\Factories;

use AcMarche\News\Models\Category;
use AcMarche\News\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<News>
 */
final class NewsFactory extends Factory
{
    #[Override]
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, asText: true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'content' => fake()->randomHtml(),
            'end_date' => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'department' => 'common',
            'category_id' => Category::factory(),
            'archive' => false,
        ];
    }
}
