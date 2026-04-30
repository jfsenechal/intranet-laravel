<?php

declare(strict_types=1);

namespace AcMarche\Pst\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

final class MagicLoginLink extends Notification
{
    use Queueable;

    public function __construct(#[SensitiveParameter] private string $token) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Use this link to sign in')
            ->line("Here's a link to login to your account without a password.")
            ->line('Note that this link expires in 24 hours and can only be used once.')
            ->action('Sign in', route('magic-login', [$notifiable, 'token' => $this->token]));
    }
}
