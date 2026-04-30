<?php

declare(strict_types=1);

namespace AcMarche\Pst\Database\Factories;

use AcMarche\Pst\Models\Odd;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Odd>
 */
final class OddFactory extends Factory
{
    #[Override]
    protected $model = Odd::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
