<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Database\Factories\AddressBookFactory;
use AcMarche\MailingList\Repositories\OwnerScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
])]
final class AddressBook extends Model
{
    use HasFactory;

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
}
