<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Database\Factories\CitoyenFactory;
use AcMarche\EmailManagement\Repository\CitoyenRepository;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A citizen mailbox account, whose directory source of truth is the LDAP ou=Citoyens.
 *
 * Edits made here change the database row only: they are not pushed to LDAP or to the
 * mail server, which is why the mailbox identity (mail, uid, home directory)
 * is not fillable: it is only set on creation, see {@see CitoyenRepository::create()}.
 * The token hashes are hidden and never rendered. The password hashes
 * live on {@see VirtualUser}.
 */
#[Connection('maria-email-management')]
#[Fillable([
    'givenName',
    'sn',
    'postalAddress',
    'postalCode',
    'l',
    'description',
    'recovery_email',
    'recovery_phone',
])]
#[Hidden([
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

    /**
     * The Dovecot login of this mailbox. citoyens.mail is a foreign key on
     * virtual_users.username, so every citoyen has one.
     *
     * @return HasOne<VirtualUser, $this>
     */
    public function virtualUser(): HasOne
    {
        return $this->hasOne(VirtualUser::class, 'username', 'mail');
    }

    protected function casts(): array
    {
        return [
            'last_connection' => 'date',
            'charter_accepted_at' => 'datetime',
        ];
    }
}
