<?php

declare(strict_types=1);

namespace AcMarche\Security\Ldap;

use LdapRecord\Models\Model;
use Override;

final class UserLdap extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    #[Override]
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    // public   $filter1 = "(&(|(sAMAccountName=$uid))(objectClass=person))";
    // public   $filter = '(&(objectClass=person)(!(uid=acmarche)))';
}
