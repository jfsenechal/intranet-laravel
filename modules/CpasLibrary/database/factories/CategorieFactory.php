<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Database\Factories;

use AcMarche\CpasLibrary\Models\Categorie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categorie>
 */
final class CategorieFactory extends Factory
{
    protected $model = Categorie::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'parent_id' => null,
            'name' => $name,
            'description' => null,
            'slug' => Str::slug($name).'-'.uniqid(),
            'icon' => null,
            'color' => null,
            'departments' => ['Cpas'],
            'public' => false,
            'users' => null,
        ];
    }
}
