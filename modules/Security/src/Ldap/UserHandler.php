<?php

declare(strict_types=1);

namespace AcMarche\Security\Ldap;

use AcMarche\Security\Repository\LdapRepository;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use LdapRecord\Models\Model;

final class UserHandler
{
    /**
     * @throws Exception
     */
    public static function createUserFromLdap(array $data): ?User
    {
        $username = $data['username'];
        if (User::where('username', $username)->first()) {
            throw new Exception('Utilisateur déjà existant');
        }
        if (($userLdap = LdapRepository::findByUsername($username)) instanceof Model) {
            $dataUser = User::generateDataFromLdap($userLdap);
            $dataUser['username'] = $username;
            $dataUser['password'] = Str::password();

            return User::create($dataUser);
        }
        throw new Exception('Utilisateur introuvable dans la LDAP');
    }
}
