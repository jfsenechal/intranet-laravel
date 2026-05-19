<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Database\Factories;

use AcMarche\Conseil\Models\Attachment;
use AcMarche\Conseil\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
final class AttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
