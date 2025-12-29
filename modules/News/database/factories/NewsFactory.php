<?php

namespace AcMarche\News\Database\Factories;

use AcMarche\News\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

final class NewsFactory extends Factory
{
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'content' => fake()->randomHtml(),
        ];
    }
}
