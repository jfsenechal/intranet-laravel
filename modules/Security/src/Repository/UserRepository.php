<?php

declare(strict_types=1);

namespace AcMarche\Security\Repository;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Security\Ldap\UserLdap;
use App\Models\User;

final class UserRepository
{
    public static string $department_selected_key = 'department_selected';

    public static function listLocalUsersForSelect(): array
    {
        return User::query()
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->username => "{$user->last_name} $user->first_name",
            ])
            ->all();
    }

    public static function find(int $userId): ?User
    {
        return User::find($userId);
    }

    public static function listLdapUsersForSelect(): array
    {
        $users = [];
        foreach (LdapRepository::allUsers() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! self::isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            $users[$username] = $userLdap->getFirstAttribute('sn').' '.$userLdap->getFirstAttribute('givenname');
        }

        asort($users, SORT_LOCALE_STRING);

        return $users;
    }

    public static function departmentSelected(): string
    {
        $department = session(self::$department_selected_key);
        if ($department) {
            return $department;
        }

        if (auth()->user() && count(auth()->user()->departments) > 0) {
            return auth()->user()->departments[0];
        }

        return DepartmentEnum::VILLE->value;
    }

    private static function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
