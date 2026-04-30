<?php

declare(strict_types=1);

namespace AcMarche\Security\Database\Factories;

use AcMarche\Security\Models\Tab;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Tab>
 */
final class TabFactory extends Factory
{
    #[Override]
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
