<?php

namespace AcMarche\Security\Database\Factories;

use AcMarche\Security\Models\Tab;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TabFactory extends Factory
{
    protected $model = Tab::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->Name(),
        ];
    }
}
