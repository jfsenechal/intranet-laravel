<?php

declare(strict_types=1);

namespace AcMarche\Pst\Database\Factories;

use AcMarche\Pst\Models\FollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<FollowUp>
 */
final class FollowUpFactory extends Factory
{
    #[Override]
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'content' => fake()->text(),
            'user_add' => fake()->userName(),
        ];
    }
}
