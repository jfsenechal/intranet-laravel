<?php

declare(strict_types=1);

namespace AcMarche\Security\Database\Factories;

use AcMarche\Security\Models\Role;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Role>
 */
#[UseModel(Role::class)]
final class RoleFactory extends Factory
{
    #[Override]
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->sentence(),
        ];
    }
}
