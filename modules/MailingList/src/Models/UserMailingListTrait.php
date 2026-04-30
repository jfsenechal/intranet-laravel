<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserMailingListTrait
{
    /**
     * @return HasMany<AddressBook, $this>
     */
    public function addressBooks(): HasMany
    {
        return $this->hasMany(AddressBook::class, 'username', 'username');
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'username', 'username');
    }

    /**
     * @return HasMany<Sender, $this>
     */
    public function senders(): HasMany
    {
        return $this->hasMany(Sender::class, 'username', 'username');
    }
}
