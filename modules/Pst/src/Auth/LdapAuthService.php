<?php

declare(strict_types=1);

namespace AcMarche\Pst\Auth;

use AcMarche\Pst\Ldap\User as UserLdap;
use App\Models\User;
use LdapRecord\Auth\PasswordRequiredException;
use LdapRecord\Auth\UsernameRequiredException;
use LdapRecord\Container;
use LdapRecord\ContainerException;

final class LdapAuthService
{
    /**
     * @throws PasswordRequiredException
     * @throws UsernameRequiredException
     * @throws ContainerException
     */
    public static function checkPassword(string $username, string $password): ?User
    {
        $user = User::where('username', '=', $username)->first();
        if (app()->environment('local', 'testing')) {

            return $user;
        }
        if ($user) {
            $userLdap = UserLdap::where('sAMAccountName', '=', $user->username)->first();
            if (! $userLdap) {

                return null;
            }
            $connection = Container::getConnection('default');

            if ($connection->auth()->attempt($userLdap->getDn(), $password)) {
                return $user;
            }
            $message = $connection->getLdapConnection()->getDiagnosticMessage();

            if (mb_strpos((string) $message, '532') !== false) {
                // "Your password has expired.";
                return null;
            }

        }

        return null;
    }
}
