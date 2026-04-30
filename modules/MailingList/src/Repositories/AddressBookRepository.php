<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Repositories;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;

final class AddressBookRepository
{
    public static function getSharingAddressBookByAddressBook(AddressBook $record)
    {
        return AddressBookShare::query()
            ->where('address_book_id', $record->id)
            ->pluck('username')
            ->all();
    }
}
