<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Repository;

use AcMarche\EmailManagement\Ldap\EmployeLdap;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ModelDoesNotExistException;
use LdapRecord\Query\Collection;

/**
 * Reads and writes staff accounts in Active Directory.
 *
 * Kept separate from AcMarche\Security\Repository\LdapRepository, which is consumed by
 * other modules and is intentionally not widened for this panel's needs.
 */
final class EmployeLdapRepository
{
    /**
     * @return Collection<int, EmployeLdap>
     */
    public function all(): Collection
    {
        return $this->query()->orderBy('samaccountname')->get();
    }

    public function getEntry(?string $samAccountName): ?EmployeLdap
    {
        if ($samAccountName === null || $samAccountName === '') {
            return null;
        }

        return $this->query()->findBy('sAMAccountName', $samAccountName);
    }

    public function getEntryByEmail(?string $mail): ?EmployeLdap
    {
        if ($mail === null || $mail === '') {
            return null;
        }

        return $this->query()->findBy('mail', $mail);
    }

    /**
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function delete(?string $samAccountName): void
    {
        $this->getEntry($samAccountName)?->delete();
    }

    /**
     * The directory does not return sAMAccountName under the default '*' selection, and it is
     * the key the local mirror is built on, so it has to be asked for by name.
     */
    private function query(): \LdapRecord\Query\Model\Builder
    {
        return EmployeLdap::query()
            ->in(config('email-management.ldap.bases.employes'))
            ->select(['*', 'samaccountname']);
    }
}
