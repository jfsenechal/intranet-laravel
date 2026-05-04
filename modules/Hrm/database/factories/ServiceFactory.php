<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Service>
 */
final class ServiceFactory extends Factory
{
    #[Override]
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'abbreviation' => Str::upper(Str::substr($name, 0, 3)),
            'direction_id' => null,
            'employer_id' => null,
            'user_add' => 'tester',
        ];
    }
}
