<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Policies;

use AcMarche\MailingList\Models\AddressBook;
use App\Models\User;

/**
 * Visibility is handled by OwnerScope (owned, shared or public address books).
 * Only the owner may update or delete an address book: shared and public
 * address books are read-only for everyone else.
 */
final class AddressBookPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return $user instanceof User;
    }

    public function update(User $user, AddressBook $addressBook): bool
    {
        return $addressBook->isOwnedBy($user);
    }

    public function delete(User $user, AddressBook $addressBook): bool
    {
        return $addressBook->isOwnedBy($user);
    }

    /**
     * Each selected record is still checked against delete() by the bulk action.
     */
    public function deleteAny(): bool
    {
        return true;
    }
}
