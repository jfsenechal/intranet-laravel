<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Database\Factories;

use AcMarche\EmailManagement\Models\Hand;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Hand::class)]
final class HandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
