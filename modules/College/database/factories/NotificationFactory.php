<?php

declare(strict_types=1);

namespace AcMarche\College\Database\Factories;

use AcMarche\College\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
final class NotificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_name' => fake()->word().'.pdf',
            'mime' => 'application/pdf',
            'updatedAt' => now(),
        ];
    }
}
