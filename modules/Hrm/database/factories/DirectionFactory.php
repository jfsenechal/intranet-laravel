<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Direction>
 */
final class DirectionFactory extends Factory
{
    #[Override]
    protected $model = Direction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'abbreviation' => Str::upper(Str::substr($name, 0, 3)),
            'director' => null,
            'employer_id' => null,
            'user_add' => 'tester',
        ];
    }
}
