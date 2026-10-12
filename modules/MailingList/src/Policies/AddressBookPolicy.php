<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Policies;

use AcMarche\MailingList\Models\AddressBook;
use App\Models\User;

/**
 * Visibility is handled by OwnerScope (owned, shared or public address books).
 * The owner and write shares may update an address book; only the owner may
 * delete it or manage its shares. Read shares and public viewers only read it.
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
        return $addressBook->isEditableBy($user);
    }

    public function delete(User $user, AddressBook $addressBook): bool
    {
        return $addressBook->isOwnedBy($user);
    }

    /**
     * Only the owner decides who the address book is shared with.
     */
    public function manageShares(User $user, AddressBook $addressBook): bool
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
