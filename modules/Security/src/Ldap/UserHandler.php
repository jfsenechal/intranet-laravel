<?php

namespace AcMarche\Security\Ldap;

use AcMarche\Security\Constant\DepartmentEnum;
use App\Ldap\User as UserLdap;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

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
        if ($userLdap = UserLdap::query()->findBy('sAMAccountName', $username)) {
            $dataUser = User::generateDataFromLdap($userLdap, $username);
            $dataUser['username'] = $username;
            $dataUser['password'] = Str::password();
            $dataUser['departments'] = DepartmentEnum::from($data['departments'][0])->value;

            return User::create($dataUser);
        }
        throw new Exception('Utilisateur introuvable dans la LDAP');
    }
}
