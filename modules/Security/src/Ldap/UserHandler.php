<?php

declare(strict_types=1);

namespace AcMarche\Security\Ldap;

use AcMarche\News\Enums\RolesEnum;
use AcMarche\Security\Models\Role;
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

            $user = User::create($dataUser);
            self::assignNewsRole($user);

            return $user;
        }
        throw new Exception('Utilisateur introuvable dans la LDAP');
    }

    /**
     * Give a newly created user the news role of the department their email
     * belongs to, so they read and receive its news right away. Only done on
     * creation: an admin may change it later and the LDAP sync must not undo that.
     */
    public static function assignNewsRole(User $user): void
    {
        $role = str_contains((string) $user->email, 'cpas.marche')
            ? RolesEnum::ROLE_NEWS_CPAS
            : RolesEnum::ROLE_NEWS_VILLE;

        if (($newsRole = Role::query()->where('name', $role->value)->first()) instanceof Role) {
            $user->addRole($newsRole);
        }
    }
}
