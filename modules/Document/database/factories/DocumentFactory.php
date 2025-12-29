<?php

namespace AcMarche\Document\Database\Factories;

use AcMarche\Document\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DocumentFactory extends Factory
{
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
        ];
    }
}
