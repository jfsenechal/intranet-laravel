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

    public static function listUsersFromLdap(): array
    {
        $users = [];
        foreach (UserLdap::all() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! self::isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            $users[$username] = $userLdap;
        }

        usort($users, function (UserLdap $a, UserLdap $b) {
            return strcasecmp($a->getFirstAttribute('sn'), $b->getFirstAttribute('sn'));
        });

        return $users;
    }

    public static function listUsersFromLdapForSelect(): array
    {
        $users = [];
        foreach (self::listUsersFromLdap() as $userLdap) {
            $users[$userLdap->getFirstAttribute('samaccountname')] = $userLdap->getFirstAttribute(
                'sn'
            ).' '.$userLdap->getFirstAttribute('givenname');
        }

        return $users;
    }

    private static function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
