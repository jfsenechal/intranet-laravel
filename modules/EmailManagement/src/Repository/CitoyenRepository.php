<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Repository;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CitoyenRepository
{
    private const string HOME_ROOT = '/var/spool/dovecot/mail/';

    /**
     * Creates the Dovecot login, then the citoyen that references it.
     *
     * Database only: nothing is written to the LDAP ou=Citoyens.
     *
     * @param  array{
     *     mail: string,
     *     password: string,
     *     quota_bytes: int|string,
     *     sn: string,
     *     givenName?: ?string,
     *     employeeNumber?: ?string,
     *     postalAddress?: ?string,
     *     postalCode?: ?string,
     *     l?: ?string,
     *     description?: ?string,
     * }  $data
     */
    public function create(array $data): Citoyen
    {
        $mail = mb_strtolower($data['mail']);
        $uid = Str::before($mail, '@');

        return DB::connection('maria-email-management')->transaction(function () use ($data, $mail, $uid): Citoyen {
            $virtualUser = new VirtualUser([
                'username' => $mail,
                'quota_bytes' => (int) $data['quota_bytes'],
                'active' => true,
            ]);
            $virtualUser->changePassword($data['password']);

            return Citoyen::forceCreate([
                'uid' => $uid,
                'mail' => $mail,
                'givenName' => $data['givenName'] ?? null,
                'sn' => $data['sn'],
                'employeeNumber' => $data['employeeNumber'] ?? null,
                'postalAddress' => $data['postalAddress'] ?? null,
                'postalCode' => $data['postalCode'] ?? null,
                'l' => $data['l'] ?? null,
                'description' => $data['description'] ?? null,
                'homeDirectory' => self::HOME_ROOT.mb_substr($uid, 0, 1).'/'.$uid,
                'auth_token' => Str::random(64),
            ]);
        });
    }
}
