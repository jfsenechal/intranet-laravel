<?php

declare(strict_types=1);

namespace AcMarche\Note\Database\Factories;

use AcMarche\Note\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Note>
 */
final class NoteFactory extends Factory
{
    #[Override]
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'user_add' => fake()->userName(),
            'is_encrypted' => false,
        ];
    }

    /**
     * A note whose content is stored encrypted.
     */
    public function encrypted(): static
    {
        return $this->state(fn (): array => ['is_encrypted' => true]);
    }
}
