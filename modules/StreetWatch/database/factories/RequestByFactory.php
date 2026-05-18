<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Database\Factories;

use AcMarche\StreetWatch\Models\RequestBy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestBy>
 */
final class RequestByFactory extends Factory
{
    protected $model = RequestBy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
