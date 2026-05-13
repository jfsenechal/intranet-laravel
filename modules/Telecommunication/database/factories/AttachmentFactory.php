<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Database\Factories;

use AcMarche\Telecommunication\Models\Attachment;
use AcMarche\Telecommunication\Models\Telephone;
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
            'telephone_id' => Telephone::factory(),
            'file_name' => fake()->uuid().'.pdf',
        ];
    }
}
