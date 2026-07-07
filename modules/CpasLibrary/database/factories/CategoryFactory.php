<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Database\Factories;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\CpasLibrary\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
            'departments' => [DepartmentEnum::CPAS->value],
            'public' => false,
            'users' => null,
        ];
    }
}
