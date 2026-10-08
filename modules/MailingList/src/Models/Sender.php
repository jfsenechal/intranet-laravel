<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\SenderFactory;
use AcMarche\MailingList\Repositories\OwnerScope;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

#[UseFactory(SenderFactory::class)]
#[ScopedBy(OwnerScope::class)]

#[Connection('maria-mailing-list')]
#[Fillable([
    'username',
    'name',
    'email',
    'footer',
    'logo',
    'smtp_host',
    'smtp_port',
    'smtp_username',
    'smtp_password',
])]
final class Sender extends Model
{
    /** @use HasFactory<SenderFactory> */
    use HasFactory;

    public function hasSmtpSettings(): bool
    {
        return filled($this->smtp_host) && filled($this->smtp_username) && filled($this->smtp_password);
    }

    /**
     * Resolve the mailer to use for this sender, configuring a dedicated SMTP
     * mailer from the sender's own settings when they are available, otherwise
     * falling back to the application's default mailer.
     */
    public function resolveMailer(): Mailer
    {
        if (! $this->hasSmtpSettings()) {
            return Mail::mailer(config('mail.default'));
        }

        $mailerName = 'sender_'.$this->id;

        config([
            "mail.mailers.{$mailerName}" => [
                'transport' => 'smtp',
                'host' => $this->smtp_host,
                'port' => $this->smtp_port ?? 587,
                'username' => $this->smtp_username,
                'password' => $this->smtp_password,
                'encryption' => 'tls',
            ],
        ]);

        return Mail::mailer($mailerName);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'smtp_password' => 'encrypted',
            'smtp_port' => 'integer',
        ];
    }
}
