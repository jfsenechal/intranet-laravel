<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use LdapRecord\LdapRecordException;

/**
 * Edits the mailbox attributes of staff accounts in Active Directory.
 *
 * Accounts are created and given passwords in the directory itself, not here: this class
 * only edits attributes of entries that already exist.
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
    public function setQuota(string $samAccountName, float $quotaMb): void
    {
        if ($quotaMb <= 0) {
            throw new Exception('Le quota doit être plus grand que 0.');
        }

        $this->employeLdapRepository->setQuota($this->requireEntry($samAccountName), $quotaMb);
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
    public function createEmail(string $samAccountName, string $mail, bool $force = false): bool
    {
        $mail = mb_trim($mail);

        if (! filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse {$mail} n'a pas un format valide.");
        }

        if (! $force) {
            $owner = $this->employeLdapRepository->findMailOwner($mail, $samAccountName);

            if ($owner !== null) {
                throw new Exception(
                    "L'adresse {$mail} est déjà utilisée par {$owner->getFirstAttribute('samaccountname')} ({$owner->getDn()})."
                );
            }
        }

        $ldapEntry = $this->requireEntry($samAccountName);

        $this->employeLdapRepository->updateEmail($ldapEntry, $mail);
        $this->employeLdapRepository->setQuota($ldapEntry, (float) config('email-management.default_quota_mb'));

        if (! $this->imapEmploye->isConfigured()) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function requireEntry(string $samAccountName): EmployeLdap
    {
        $ldapEntry = $this->employeLdapRepository->getEntry($samAccountName);

        if (! $ldapEntry instanceof EmployeLdap) {
            throw new Exception("{$samAccountName} est introuvable dans l'annuaire.");
        }

        return $ldapEntry;
    }
}
