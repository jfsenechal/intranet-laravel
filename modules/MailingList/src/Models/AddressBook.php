<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\AddressBookFactory;
use AcMarche\MailingList\Enums\SharePermission;
use AcMarche\MailingList\Repositories\OwnerScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(AddressBookFactory::class)]
#[ScopedBy(OwnerScope::class)]

#[Connection('maria-mailing-list')]
#[Fillable([
    'username',
    'name',
    'is_public',
])]
final class AddressBook extends Model
{
    use HasFactory;

    public function isOwnedBy(User $user): bool
    {
        return $this->username === $user->username;
    }

    /**
     * The owner and the users it is shared with in write mode may edit the
     * address book and its contacts; read shares and public viewers only read it.
     */
    public function isEditableBy(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        return $this->shares()
            ->where('username', $user->username)
            ->where('permission', SharePermission::Write)
            ->exists();
    }

    public function isSharedWith(User $user): bool
    {
        return $this->shares()->where('username', $user->username)->exists();
    }

    /**
     * Address books a user may send emails to: their own, the ones shared
     * with them and the public ones.
     *
     * @param  Builder<AddressBook>  $query
     */
    public function scopeMailableBy(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('username', $user->username)
                ->orWhere('is_public', true)
                ->orWhereHas('shares', fn (Builder $query) => $query->where('username', $user->username));
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)->withTimestamps();
    }

    /**
     * @return HasMany<AddressBookShare, $this>
     */
    public function shares(): HasMany
    {
        return $this->hasMany(AddressBookShare::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function sharedWithUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'address_book_shares', 'address_book_id', 'username', 'id', 'username')
            ->withPivot('permission')
            ->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
