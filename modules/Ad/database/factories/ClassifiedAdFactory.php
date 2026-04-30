<?php

declare(strict_types=1);

namespace AcMarche\Ad\Database\Factories;

use AcMarche\Ad\Models\Category;
use AcMarche\Ad\Models\ClassifiedAd;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<ClassifiedAd>
 */
final class ClassifiedAdFactory extends Factory
{
    #[Override]
    protected $model = ClassifiedAd::class;

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
