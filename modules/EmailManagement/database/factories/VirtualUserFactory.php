<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Database\Factories;

use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

#[UseModel(VirtualUser::class)]
final class VirtualUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName().'@marche.be',
            'userPassword' => null,
            'legacy_password' => '{SSHA}'.Str::random(32),
            'quota_bytes' => 250,
            'active' => true,
        ];
    }
}
