<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Attributes\AccountControl;

/**
 * Write-through for staff accounts: Active Directory first, then the local mirror.
 *
 * Order matters. If AD rejects a write the mirror must not be left holding a row that
 * does not exist in the directory, so nothing is persisted locally until AD has
 * accepted the change.
 */
final class EmployeHandler
{
    public function __construct(private readonly EmployeLdapRepository $employeLdapRepository) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Exception
     * @throws LdapRecordException
     */
    public function createEmploye(array $data): Employe
    {
        $samAccountName = $data['samaccountname'];

        if ($this->employeLdapRepository->getEntry($samAccountName) instanceof EmployeLdap) {
            throw new Exception("L'identifiant {$samAccountName} existe déjà dans l'annuaire.");
        }

        if (Employe::where('samaccountname', $samAccountName)->exists()) {
            throw new Exception("L'identifiant {$samAccountName} existe déjà localement.");
        }

        $fullName = $this->fullName($data['givenName'] ?? null, $data['sn']);

        $ldapEntry = new EmployeLdap;
        $ldapEntry->cn = $fullName;
        $ldapEntry->samaccountname = $samAccountName;
        $ldapEntry->givenName = $data['givenName'] ?? null;
        $ldapEntry->sn = $data['sn'];
        $ldapEntry->displayName = $fullName;
        $ldapEntry->mail = $data['mail'];
        $ldapEntry->userPrincipalName = $data['mail'];
        $ldapEntry->description = $data['description'] ?? null;
        $ldapEntry->telephoneNumber = $data['telephoneNumber'] ?? null;
        $ldapEntry->unicodepwd = $data['password'];
        $ldapEntry->userAccountControl = (new AccountControl)
            ->add(AccountControl::NORMAL_ACCOUNT)
            ->getValue();

        $ldapEntry->inside(config('email-management.ldap.bases.employes'))->save();

        return Employe::create([
            ...Employe::generateDataFromLdap($ldapEntry),
            'sync_at' => now(),
        ]);
    }

    /**
     * Pushes the mirror's current state back to the directory entry.
     *
     * @throws LdapRecordException
     */
    public function updateEmploye(Employe $employe, EmployeLdap $ldapEntry): void
    {
        $ldapEntry->givenName = $employe->givenName;
        $ldapEntry->sn = $employe->sn;
        $ldapEntry->displayName = $this->fullName($employe->givenName, $employe->sn);
        $ldapEntry->description = $employe->description;
        $ldapEntry->telephoneNumber = $employe->telephoneNumber;
        $ldapEntry->mail = $employe->mail;

        $ldapEntry->save();
    }

    /**
     * Pulls every directory entry into the mirror, keyed on sAMAccountName.
     *
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

        Employe::whereNotIn('samaccountname', $seen)->delete();

        return count($seen);
    }

    private function fullName(?string $givenName, ?string $sn): string
    {
        return mb_trim(($givenName ?? '').' '.($sn ?? ''));
    }
}
