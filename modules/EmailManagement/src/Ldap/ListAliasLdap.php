<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Ldap;

use LdapRecord\Models\Model;

/**
 * A mailing list or a service in Active Directory, under OU=LISTS or OU=SERVICES.
 *
 * Identity is carried by cn. The members of the list are the values of proxyAddresses:
 * the legacy GestEmail wrote them there verbatim, with no smtp: prefix, and the mail
 * server still reads them that way.
 *
 * Extends the bare LdapRecord model rather than ActiveDirectory\Group: nothing here needs
 * the AD group helpers, and the legacy ListAlias was built the same way.
 */
final class ListAliasLdap extends Model
{
    /** @var array<int, string> */
    public static array $objectClasses = ['group'];

    protected ?string $connection = 'default';
}
