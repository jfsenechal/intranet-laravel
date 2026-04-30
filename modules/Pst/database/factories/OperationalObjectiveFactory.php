<?php

declare(strict_types=1);

namespace AcMarche\Pst\Database\Factories;

use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Models\OperationalObjective;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<OperationalObjective>
 */
final class OperationalObjectiveFactory extends Factory
{
    #[Override]
    protected $model = OperationalObjective::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'position' => fake()->numberBetween(1, 100),
            'department' => 'VILLE',
            'scope' => ActionScopeEnum::EXTERNAL,
        ];
    }

    public function internal(): self
    {
        return $this->state([
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);
    }
}
