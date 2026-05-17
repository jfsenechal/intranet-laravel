<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Database\Factories;

use AcMarche\CpasLibrary\Models\Categorie;
use AcMarche\CpasLibrary\Models\Fiche;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fiche>
 */
final class FicheFactory extends Factory
{
    protected $model = Fiche::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => CategorieFactory::new(),
            'type' => 'default',
            'source' => null,
            'date_promulgation' => null,
            'date_publication' => null,
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'userAdd' => fake()->userName(),
            'mimeType' => null,
            'createdAt' => now(),
            'updatedAt' => now(),
            'fileName' => null,
            'fileSize' => null,
            'slug' => null,
            'date_rappel' => null,
            'type_document' => null,
            'date_begin' => null,
            'date_end' => null,
        ];
    }

    public function withCategory(Categorie $categorie): self
    {
        return $this->state(['category_id' => $categorie->id]);
    }
}
