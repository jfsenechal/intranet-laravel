<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use LdapRecord\LdapRecordException;
use LdapRecord\Models\ActiveDirectory\User;

/**
 * A staff account in Active Directory, under OU=AC,OU=MUSERS.
 *
 * Identity is carried by sAMAccountName. Extends LdapRecord's Active Directory user so
 * that password writes go through unicodePwd (which AD only accepts over the ldaps://
 * connection this module uses) and userAccountControl is handled by HasAccountControl,
 * rather than being hand-rolled here.
 */
final class EmployeLdap extends User
{
    protected ?string $connection = 'default';

    public static function describe(\Exception|LdapRecordException $e): string
    {
        $error = $e->getMessage();

        if ($e instanceof LdapRecordException && $e->getDetailedError()) {
            $error .= ' '.$e->getDetailedError()->getDiagnosticMessage();
        }

        return $error;
    }
}
