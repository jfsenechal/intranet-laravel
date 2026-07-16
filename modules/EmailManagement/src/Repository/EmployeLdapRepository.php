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
     * The mailbox quota, in megabytes.
     *
     * Carried by otherPager: Active Directory has no quota attribute of its own, and the
     * mail server reads the limit from this one.
     *
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function setQuota(EmployeLdap $ldapEntry, float $quotaMb): void
    {
        $ldapEntry->setAttribute('otherPager', (string) $quotaMb);
        $ldapEntry->update();
    }

    public function getQuota(EmployeLdap $ldapEntry): ?float
    {
        $quota = $ldapEntry->getFirstAttribute('otherpager');

        return is_numeric($quota) ? (float) $quota : null;
    }

    /**
     * Replaces the entry's alias addresses wholesale.
     *
     * @param  array<int, string>  $aliases
     *
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function updateAliases(EmployeLdap $ldapEntry, array $aliases): void
    {
        $ldapEntry->setAttribute('proxyAddresses', array_values($aliases));
        $ldapEntry->update();
    }

    /**
     * @return array<int, string>
     */
    public function getAliases(EmployeLdap $ldapEntry): array
    {
        return array_values(array_filter((array) $ldapEntry->getAttribute('proxyaddresses')));
    }

    /**
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function updateEmail(EmployeLdap $ldapEntry, string $mail): void
    {
        $ldapEntry->setAttribute('mail', $mail);
        $ldapEntry->setAttribute('userPrincipalName', $mail);
        $ldapEntry->update();
    }

    /**
     * The entry already using an address, as mail or as an alias.
     *
     * Used to refuse handing the same address to two accounts. Mirrors the legacy
     * LdapCommon::checkMailExist, which searched mail and proxyAddresses.
     */
    public function findMailOwner(string $mail, ?string $exceptSamAccountName = null): ?EmployeLdap
    {
        $matches = $this->query()
            ->orWhere('mail', '=', $mail)
            ->orWhere('proxyAddresses', '=', $mail)
            ->orWhere('userPrincipalName', '=', $mail)
            ->get();

        foreach ($matches as $match) {
            if ($match->getFirstAttribute('samaccountname') !== $exceptSamAccountName) {
                return $match;
            }
        }

        return null;
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
