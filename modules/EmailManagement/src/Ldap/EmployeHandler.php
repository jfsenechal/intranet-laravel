<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use AcMarche\EmailManagement\Models\EmailDto;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\Security\Repository\LdapRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\ModelDoesNotExistException;

final class EmployeHandler
{
    public function __construct(private readonly LdapRepository $ldapRepository) {}

    /**
     * @throws Exception
     */
    public static function createCitoyenDbFromLdap(EmployeLdap $data): ?Employe
    {
        if (Employe::where('uid', $data->getFirstAttribute('uid'))->first()) {
            throw new Exception('Utilisateur déjà existant');
        }
        $dataUser = Employe::generateDataFromLdap($data);
        $dataUser['userPassword'] = Str::password();
        $dataUser['auth_token'] = Str::random(64);

        return Employe::create($dataUser);
    }

    /**
     * @throws Exception
     * @throws LdapRecordException
     */
    public function createCitoyen(array $data): Employe
    {
        $emailDto = new EmailDto;
        $emailDto->givenName = $data['givenName'];
        $emailDto->sn = $data['sn'];
        $emailDto->mail = $data['mail'];
        $emailDto->postalAddress = $data['postalAddress'];
        $emailDto->postalCode = $data['postalCode'];
        $emailDto->l = $data['l'];
        $emailDto->employeeNumber = $data['employeeNumber'];
        $emailDto->userPassword = $data['password'];
        $emailDto->gosaMailQuota = (int) ($data['gosaMailQuota'] ?? 350);
        $emailDto->description = $data['description'] ?? null;

        $ldapEntry = $this->ldapRepository->createCitizen($emailDto);

        return Employe::create([
            ...Employe::generateDataFromLdap($ldapEntry),
            'auth_token' => Str::random(64),
        ]);
    }

    /**
     * @throws LdapRecordException
     * @throws ModelDoesNotExistException
     */
    public function updateCitoyen(Employe|Model $employe, EmployeLdap $ldapEntry): void
    {
        $ldapEntry->setAttribute('givenName', $employe->givenName);
        $ldapEntry->setAttribute('sn', $employe->sn);
        $ldapEntry->setAttribute('cn', mb_trim($employe->givenName.' '.$employe->sn));
        $ldapEntry->setAttribute('description', $employe->description);
        $ldapEntry->setAttribute('employeeNumber', $employe->employeeNumber);
        $ldapEntry->setAttribute('postalAddress', $employe->postalAddress);
        $ldapEntry->setAttribute('postalCode', $employe->postalCode);
        $ldapEntry->setAttribute('l', $employe->l);

        $ldapEntry->update();
    }

    /**
     * @throws Exception
     */
    public function changeQuota(Employe|Model $employe, int $quota): void
    {
        $ldapEntry = $this->ldapRepository->findByUsername($employe->uid);

        if (! $ldapEntry) {
            throw new Exception('Utilisateur LDAP introuvable');
        }

        $this->ldapRepository->updateQuota($ldapEntry, $quota);
        $employe->update(['gosaMailQuota' => $quota]);
    }
}
