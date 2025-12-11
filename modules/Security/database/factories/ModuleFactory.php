<?php

namespace AcMarche\Security\Database\Factories;

use AcMarche\Security\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'url' => fake()->url(),
            'description' => fake()->sentence(),
            'is_external' => fake()->boolean(20),
            'is_public' => fake()->boolean(30),
            'icon' => 'heroicon-o-cube',
            'color' => fake()->hexColor(),
        ];
    }
}
