<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Repository;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CitoyenRepository
{
    private const int DEFAULT_QUOTA = 350;

    /**
     * Creates the Dovecot login, then the citoyen that references it.
     *
     * @param  array{
     *     mail: string,
     *     password: string,
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

        return DB::connection('maria-email-management')->transaction(function () use ($data, $mail): Citoyen {
            $virtualUser = new VirtualUser([
                'username' => $mail,
                'quota_bytes' => self::DEFAULT_QUOTA,
                'active' => true,
            ]);
            $virtualUser->changePassword($data['password']);

            return Citoyen::forceCreate([
                'mail' => $mail,
                'givenName' => $data['givenName'] ?? null,
                'sn' => $data['sn'],
                'employeeNumber' => $data['employeeNumber'] ?? null,
                'postalAddress' => $data['postalAddress'] ?? null,
                'postalCode' => $data['postalCode'] ?? null,
                'l' => $data['l'] ?? null,
                'description' => $data['description'] ?? null,
                'homeDirectory' => Citoyen::homeDirectoryFor($mail),
                'auth_token' => Str::random(64),
            ]);
        });
    }
}
