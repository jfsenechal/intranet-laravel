<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Ldap\User as UserLdap;
use App\Models\User;

final class UserRepository
{
    public static function getUsersForSelect(): array
    {
        $users = [];
        foreach (User::all() as $user) {
            $users[$user->id] = $user->first_name.' '.$user->last_name;
        }

        return $users;
    }

    public static function find(int $userId): ?User
    {
        return User::find($userId);
    }

    public static function listUsersFromLdapForSelect(): array
    {
        $users = [];
        foreach (UserLdap::all() as $userLdap) {
            if (!$userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (!self::isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            $users[$username] = $userLdap->getFirstAttribute('sn').' '.$userLdap->getFirstAttribute('givenname');
        }

        asort($users, SORT_LOCALE_STRING);

        return $users;
    }

    private static function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
