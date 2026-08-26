<?php

declare(strict_types=1);

namespace AcMarche\App\Database\Factories;

use AcMarche\App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Article>
 */
final class ArticleFactory extends Factory
{
    #[Override]
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'excerpt' => fake()->sentence(12),
            'body' => '<p>'.fake()->paragraph().'</p>',
        ];
    }
}
