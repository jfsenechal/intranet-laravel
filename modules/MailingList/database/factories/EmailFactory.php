<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Factories;

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\Sender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Email>
 */
final class EmailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'sender_id' => Sender::factory(),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'attachments' => null,
            'status' => EmailStatus::Draft,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Sent,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Sending,
        ]);
    }
}
