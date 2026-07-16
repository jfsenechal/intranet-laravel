<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Database\Factories\EmployeFactory;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Local mirror of a staff account held in Active Directory (OU=AC,OU=MUSERS).
 *
 * Active Directory is the source of truth: this table is a read mirror kept in step
 * by write-through on create/update and by the sync command. Passwords are never
 * mirrored -- they live in AD and are checked there.
 */
#[Connection('maria-email-management')]
#[Fillable([
    'samaccountname',
    'givenName',
    'sn',
    'cn',
    'displayName',
    'mail',
    'dn',
    'description',
    'telephoneNumber',
    'last_connection',
    'protocol_connection',
    'port_connection',
    'secure_connection',
    'sync_at',
])]
#[UseFactory(EmployeFactory::class)]
final class Employe extends Model
{
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public static function generateDataFromLdap(EmployeLdap $userLdap): array
    {
        return [
            'samaccountname' => $userLdap->getFirstAttribute('samaccountname'),
            'givenName' => $userLdap->getFirstAttribute('givenName'),
            'sn' => $userLdap->getFirstAttribute('sn'),
            'cn' => $userLdap->getFirstAttribute('cn'),
            'displayName' => $userLdap->getFirstAttribute('displayName'),
            'dn' => $userLdap->getDn(),
            'mail' => $userLdap->getFirstAttribute('mail'),
            'description' => $userLdap->getFirstAttribute('description'),
            'telephoneNumber' => $userLdap->getFirstAttribute('telephoneNumber'),
        ];
    }

    public function getFullName(): string
    {
        return mb_trim($this->givenName.' '.$this->sn);
    }

    protected function email(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->mail);
    }

    protected function casts(): array
    {
        return [
            'last_connection' => 'date',
            'secure_connection' => 'boolean',
            'port_connection' => 'integer',
            'sync_at' => 'datetime',
        ];
    }
}
