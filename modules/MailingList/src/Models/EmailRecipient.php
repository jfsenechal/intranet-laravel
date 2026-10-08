<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\EmailRecipientFactory;
use AcMarche\MailingList\Enums\RecipientStatus;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

#[UseFactory(EmailRecipientFactory::class)]

#[Connection('maria-mailing-list')]
#[Fillable([
    'email_id',
    'contact_id',
    'email_address',
    'name',
    'status',
    'error',
    'sent_at',
    'unsubscribed_at',
])]
final class EmailRecipient extends Model
{
    /** @use HasFactory<EmailRecipientFactory> */
    use HasFactory;

    /**
     * Generate a signed, public URL the recipient can use to unsubscribe.
     */
    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('unsubscribe.show', ['recipient' => $this->getKey()]);
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    /**
     * Mark this recipient (and its linked contact) as unsubscribed.
     */
    public function markAsUnsubscribed(): void
    {
        if ($this->isUnsubscribed()) {
            return;
        }

        $this->forceFill(['unsubscribed_at' => now()])->save();

        $this->contact()
            ->withoutGlobalScopes()
            ->whereNull('unsubscribed_at')
            ->update(['unsubscribed_at' => now()]);
    }

    /**
     * @return BelongsTo<Email, $this>
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RecipientStatus::class,
            'sent_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
