<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Repository;

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ModelDoesNotExistException;
use LdapRecord\Query\Collection;

/**
 * Reads and writes the mail groups of OU=LISTS and OU=SERVICES.
 *
 * Kept beside EmployeLdapRepository and shaped like it: the two read different OUs with
 * different attributes, and merging them would widen both.
 */
final class ListLdapRepository
{
    /**
     * @return Collection<int, ListAliasLdap>
     */
    public function all(ListOuEnum $ou): Collection
    {
        return $this->query($ou)->orderBy('cn')->get();
    }

    /**
     * The legacy searchList matched proxyAddresses only, so looking a list up by its own
     * address found nothing. Matching mail and cn as well is the point of this port.
     *
     * @return Collection<int, ListAliasLdap>
     */
    public function search(ListOuEnum $ou, string $term): Collection
    {
        return $this->query($ou)
            ->orWhere('mail', 'contains', $term)
            ->orWhere('proxyAddresses', 'contains', $term)
            ->orWhere('cn', 'contains', $term)
            ->get();
    }

    public function getEntry(ListOuEnum $ou, ?string $cn): ?ListAliasLdap
    {
        if ($cn === null || $cn === '') {
            return null;
        }

        return $this->query($ou)->findBy('cn', $cn);
    }

    /**
     * The groups a mail address is a member of.
     *
     * @return Collection<int, ListAliasLdap>
     */
    public function memberOfLists(ListOuEnum $ou, string $mail): Collection
    {
        return $this->query($ou)->where('proxyAddresses', 'contains', $mail)->get();
    }

    /**
     * @return array<int, string>
     */
    public function getMembers(ListAliasLdap $entry): array
    {
        return array_values(array_filter((array) $entry->getAttribute('proxyaddresses')));
    }

    /**
     * Replaces the members wholesale, so an empty array clears the list.
     *
     * @param  array<int, string>  $members
     *
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function updateMembers(ListAliasLdap $entry, array $members): void
    {
        $entry->setAttribute('proxyAddresses', array_values($members));
        $entry->update();
    }

    /**
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function updateDescription(ListAliasLdap $entry, ?string $description): void
    {
        $entry->setAttribute('description', $description);
        $entry->update();
    }

    /**
     * cn is selected by name on top of '*' because all() sorts by it: orderBy() asks the
     * directory for a server-side sort, and Active Directory returns the sort key stripped from
     * every entry unless it is named in the selection. cn is the identity of a list and the key
     * the table rows are tracked by, so losing it collapses every row into one.
     */
    private function query(ListOuEnum $ou): \LdapRecord\Query\Model\Builder
    {
        return ListAliasLdap::query()->in($ou->baseDn())->select(['*', 'cn']);
    }
}
