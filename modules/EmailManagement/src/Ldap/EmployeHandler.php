<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use LdapRecord\LdapRecordException;

/**
 * Keeps the local staff mirror and Active Directory in step.
 *
 * Accounts are created and given passwords in the directory itself, not here: this class
 * only edits attributes of entries that already exist, and pulls the directory into the
 * mirror.
 */
final class EmployeHandler
{
    public function __construct(
        private readonly EmployeLdapRepository $employeLdapRepository,
        private readonly ImapEmploye $imapEmploye,
    ) {}

    /**
     * Sets the mailbox quota, in megabytes.
     *
     * @throws Exception
     * @throws LdapRecordException
     */
    public function setQuota(Employe $employe, float $quotaMb): void
    {
        if ($quotaMb <= 0) {
            throw new Exception('Le quota doit être plus grand que 0.');
        }

        $this->employeLdapRepository->setQuota($this->requireEntry($employe), $quotaMb);
    }

    /**
     * Replaces the account's alias addresses.
     *
     * @param  array<int, string>  $aliases
     *
     * @throws Exception
     * @throws LdapRecordException
     */
    public function updateAliases(Employe $employe, array $aliases): void
    {
        $aliases = array_values(array_unique(array_filter(array_map(
            static fn (string $alias): string => mb_trim($alias),
            $aliases,
        ))));

        foreach ($aliases as $alias) {
            if (! filter_var($alias, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("L'alias {$alias} n'a pas un format valide.");
            }

            $owner = $this->employeLdapRepository->findMailOwner($alias, $employe->samaccountname);

            if ($owner !== null) {
                throw new Exception("L'adresse {$alias} est déjà utilisée par {$owner->getFirstAttribute('samaccountname')}.");
            }
        }

        $this->employeLdapRepository->updateAliases($this->requireEntry($employe), $aliases);
    }

    /**
     * Gives the account an address: writes it to the directory, applies the default quota
     * and creates the mailbox.
     *
     * The directory is written first. A mailbox without a matching directory entry is
     * invisible to the mail server, whereas an address whose mailbox creation failed can
     * be repaired by running this again.
     *
     * @return bool whether the mailbox was created — false when IMAP is not configured
     *
     * @throws Exception
     * @throws LdapRecordException
     */
    public function createEmail(Employe $employe, string $mail, bool $force = false): bool
    {
        $mail = mb_trim($mail);

        if (! filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse {$mail} n'a pas un format valide.");
        }

        if (! $force) {
            $owner = $this->employeLdapRepository->findMailOwner($mail, $employe->samaccountname);

            if ($owner !== null) {
                throw new Exception(
                    "L'adresse {$mail} est déjà utilisée par {$owner->getFirstAttribute('samaccountname')} ({$owner->getDn()})."
                );
            }
        }

        $ldapEntry = $this->requireEntry($employe);

        $this->employeLdapRepository->updateEmail($ldapEntry, $mail);
        $this->employeLdapRepository->setQuota($ldapEntry, (float) config('email-management.default_quota_mb'));

        $employe->update(['mail' => $mail, 'sync_at' => now()]);

        if (! $this->imapEmploye->isConfigured()) {
            return false;
        }

        $this->imapEmploye->createMailBox($employe->samaccountname);

        return true;
    }

    /**
     * Pushes the mirror's current state back to the directory entry.
     *
     * A blank field is written as an empty array rather than an empty string, which is how
     * LdapRecord removes an attribute: Active Directory rejects an empty string value.
     *
     * @throws LdapRecordException
     */
    public function updateEmploye(Employe $employe, EmployeLdap $ldapEntry): void
    {
        foreach (Employe::LDAP_IDENTITY_ATTRIBUTES as $attribute) {
            $value = $employe->{$attribute};

            $ldapEntry->setAttribute($attribute, blank($value) ? [] : $value);
        }

        $ldapEntry->displayName = $this->fullName($employe->givenName, $employe->sn);
        $ldapEntry->mail = $employe->mail;

        $ldapEntry->save();
    }

    /**
     * Pulls every directory entry into the mirror, keyed on sAMAccountName.
     *
     * Entries absent from the directory are pruned, so an empty read is treated as a fault
     * rather than as "the directory is empty" — otherwise it would wipe the mirror.
     *
     * @throws Exception
     * @throws LdapRecordException
     */
    public function syncFromLdap(): int
    {
        $seen = [];

        foreach ($this->employeLdapRepository->all() as $ldapEntry) {
            $samAccountName = $ldapEntry->getFirstAttribute('samaccountname');

            if ($samAccountName === null || $samAccountName === '') {
                continue;
            }

            Employe::updateOrCreate(
                ['samaccountname' => $samAccountName],
                [...Employe::generateDataFromLdap($ldapEntry), 'sync_at' => now()],
            );

            $seen[] = $samAccountName;
        }

        if ($seen === []) {
            throw new Exception("L'annuaire n'a renvoyé aucun employé exploitable. Aucune fiche locale n'a été supprimée.");
        }

        Employe::whereNotIn('samaccountname', $seen)->delete();

        return count($seen);
    }

    /**
     * @throws Exception
     */
    private function requireEntry(Employe $employe): EmployeLdap
    {
        $ldapEntry = $this->employeLdapRepository->getEntry($employe->samaccountname);

        if (! $ldapEntry instanceof EmployeLdap) {
            throw new Exception("{$employe->samaccountname} est introuvable dans l'annuaire.");
        }

        return $ldapEntry;
    }

    private function fullName(?string $givenName, ?string $sn): string
    {
        return mb_trim(($givenName ?? '').' '.($sn ?? ''));
    }
}
