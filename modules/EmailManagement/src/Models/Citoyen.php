<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Database\Factories\CitoyenFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A citizen mailbox account, whose directory source of truth is the LDAP ou=Citoyens.
 *
 * Edits made here change the database row only: they are not pushed to LDAP or to the
 * mail server, which is why the mailbox identity (mail, uid, dn, quota, home directory)
 * is not fillable. The password and token hashes are hidden and never rendered.
 */
#[Connection('maria-email-management')]
#[Fillable([
    'givenName',
    'sn',
    'cn',
    'postalAddress',
    'postalCode',
    'l',
    'description',
    'recovery_email',
    'recovery_phone',
])]
#[Hidden([
    'userPassword',
    'legacy_password',
    'auth_token',
    'remember_token',
])]
#[UseFactory(CitoyenFactory::class)]
final class Citoyen extends Model
{
    use HasFactory;

    public function getFullName(): string
    {
        return mb_trim($this->givenName.' '.$this->sn);
    }

    protected function casts(): array
    {
        return [
            'gosaMailQuota' => 'float',
            'last_connection' => 'date',
            'charter_accepted_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }
}
