<?php

declare(strict_types=1);

namespace AcMarche\Ad\Database\Factories;

use AcMarche\Ad\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Subscriber>
 */
final class SubscriberFactory extends Factory
{
    #[Override]
    protected $model = Subscriber::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ];
    }
}
