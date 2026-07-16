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
    'info',
    'title',
    'company',
    'department',
    'co',
    'initials',
    'wWWHomePage',
    'streetAddress',
    'postalCode',
    'l',
    'telephoneNumber',
    'ipPhone',
    'mobile',
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
     * The directory attributes the mirror carries, in the casing the directory uses.
     *
     * Everything here is round-tripped: pulled by generateDataFromLdap and pushed back by
     * EmployeHandler::updateEmploye. Identity attributes only -- mail, quota and aliases are
     * handled by their own actions, and cn/displayName are derived rather than edited.
     *
     * @var array<int, string>
     */
    public const array LDAP_IDENTITY_ATTRIBUTES = [
        'givenName',
        'sn',
        'initials',
        'title',
        'company',
        'department',
        'description',
        'info',
        'wWWHomePage',
        'streetAddress',
        'postalCode',
        'l',
        'co',
        'telephoneNumber',
        'ipPhone',
        'mobile',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function generateDataFromLdap(EmployeLdap $userLdap): array
    {
        $data = [
            'samaccountname' => $userLdap->getFirstAttribute('samaccountname'),
            'cn' => $userLdap->getFirstAttribute('cn'),
            'displayName' => $userLdap->getFirstAttribute('displayName'),
            'dn' => $userLdap->getDn(),
            'mail' => $userLdap->getFirstAttribute('mail'),
        ];

        foreach (self::LDAP_IDENTITY_ATTRIBUTES as $attribute) {
            $data[$attribute] = $userLdap->getFirstAttribute($attribute);
        }

        return $data;
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
