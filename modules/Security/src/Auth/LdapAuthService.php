<?php

declare(strict_types=1);

namespace AcMarche\Security\Auth;

use AcMarche\Security\Repository\LdapRepository;
use App\Models\User;
use LdapRecord\Auth\PasswordRequiredException;
use LdapRecord\Auth\UsernameRequiredException;
use LdapRecord\Container;
use LdapRecord\ContainerException;
use LdapRecord\Models\Model;

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
        if (app()->environment('local')) {

            return $user;
        }
        if ($user) {
            $userLdap = LdapRepository::findByUsername((string) $user->username);
            if (! $userLdap instanceof Model) {

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
