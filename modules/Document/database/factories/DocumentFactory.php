<?php

declare(strict_types=1);

namespace AcMarche\Document\Database\Factories;

use AcMarche\Document\Models\Category;
use AcMarche\Document\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Document>
 */
final class DocumentFactory extends Factory
{
    #[Override]
    protected $model = Document::class;

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
            'file_path' => 'uploads/document/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'file_size' => fake()->numberBetween(1024, 10240),
            'file_mime' => 'application/pdf',
            'category_id' => Category::factory(),
        ];
    }
}
