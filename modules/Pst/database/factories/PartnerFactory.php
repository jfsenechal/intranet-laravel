<?php

declare(strict_types=1);

namespace AcMarche\Pst\Database\Factories;

use AcMarche\Pst\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Partner>
 */
final class PartnerFactory extends Factory
{
    #[Override]
    protected $model = Partner::class;

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
