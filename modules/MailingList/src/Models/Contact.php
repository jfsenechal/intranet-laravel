<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\ContactFactory;
use AcMarche\MailingList\Repositories\OwnerScope;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ContactFactory::class)]
#[ScopedBy(OwnerScope::class)]

#[Connection('maria-mailing-list')]
#[Fillable([
    'username',
    'last_name',
    'first_name',
    'email',
    'description',
    'phone',
    'unsubscribed_at',
])]
final class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    /**
     * @param  Builder<Contact>  $query
     */
    public function scopeSubscribed(Builder $query): void
    {
        $query->whereNull('unsubscribed_at');
    }

    /**
     * @return BelongsToMany<AddressBook, $this>
     */
    public function addressBooks(): BelongsToMany
    {
        return $this->belongsToMany(AddressBook::class)->withTimestamps();
    }

    /**
     * @return HasMany<ContactShare, $this>
     */
    public function shares(): HasMany
    {
        return $this->hasMany(ContactShare::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function sharedWithUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contact_shares', 'contact_id', 'username', 'id', 'username')
            ->withPivot('permission')
            ->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }
}
