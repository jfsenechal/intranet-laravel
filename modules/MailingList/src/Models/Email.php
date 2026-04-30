<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\EmailFactory;
use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Repositories\OwnerScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(EmailFactory::class)]
#[ScopedBy(OwnerScope::class)]
#[Connection('maria-mailing-list')]
#[Fillable([
    'username',
    'sender_id',
    'subject',
    'body',
    'attachments',
    'status',
    'batch_id',
    'sent_count',
    'total_count',
])]
final class Email extends Model
{
    /** @use HasFactory<EmailFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * @return BelongsTo<Sender, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(Sender::class);
    }

    /**
     * @return HasMany<EmailRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'status' => EmailStatus::class,
        ];
    }
}
