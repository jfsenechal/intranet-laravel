<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Database\Factories;

use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailRecipient>
 */
final class EmailRecipientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_id' => Email::factory(),
            'contact_id' => Contact::factory(),
            'email_address' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => RecipientStatus::Pending,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => RecipientStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => RecipientStatus::Failed,
            'error' => fake()->sentence(),
        ]);
    }
}
