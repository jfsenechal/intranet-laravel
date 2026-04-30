<?php

declare(strict_types=1);

namespace AcMarche\Publication\Database\Factories;

use AcMarche\Publication\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Publication>
 */
final class PublicationFactory extends Factory
{
    #[Override]
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'url' => fake()->url(),
            'expire_date' => fake()->dateTimeBetween('+1 month', '+1 year'),
            'user_add' => fake()->userName(),
        ];
    }
}
