<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Casts\EncryptedPersonalDataCast;
use AcMarche\EmailManagement\Database\Factories\CitoyenFactory;
use AcMarche\EmailManagement\Repository\CitoyenRepository;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A citizen mailbox account. The database is the source of truth: citoyens are not in LDAP.
 *
 * The mailbox identity (mail, home directory) is not fillable: it is only set on
 * creation, see {@see CitoyenRepository::create()}. The token hashes are hidden and never
 * rendered. The password hashes live on {@see VirtualUser}, which Dovecot reads.
 *
 * The personal data (employeeNumber, postalAddress, l, recovery_*) is encrypted with the key
 * shared with gestmail, see {@see EncryptedPersonalDataCast}: those columns cannot be searched,
 * sorted or filtered in SQL.
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
    'hand_password',
    'remember_token',
])]
#[UseFactory(CitoyenFactory::class)]
final class Citoyen extends Model
{
    use HasFactory;

    /**
     * The mailbox path, built like the home of the Dovecot userdb query.
     */
    public static function homeDirectoryFor(string $mail): string
    {
        return config('email-management.mail_root').'/'.Str::afterLast($mail, '@').'/'.mb_strtolower(mb_substr($mail, 0, 1)).'/'.$mail;
    }

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

    public function isTakenOver(): bool
    {
        return filled($this->hand_password);
    }

    /**
     * Replaces the password with a temporary one, keeping the hash Dovecot currently uses.
     * Most logins still rely on legacy_password, which changing the password clears. A second
     * call only replaces the temporary password, so the kept hash is never overwritten.
     */
    public function takeOver(string $temporaryPassword): void
    {
        $virtualUser = $this->virtualUser;

        DB::connection('maria-email-management')->transaction(function () use ($virtualUser, $temporaryPassword): void {
            if (! $this->isTakenOver()) {
                $this->forceFill([
                    'hand_password' => $virtualUser->userPassword ?? $virtualUser->legacy_password,
                ])->save();
            }

            $virtualUser->changePassword($temporaryPassword);
        });
    }

    /**
     * Puts the kept hash back. It may be an {SSHA} one: Dovecot reads the scheme from the
     * prefix, whichever column the hash is in.
     */
    public function restorePassword(): void
    {
        $virtualUser = $this->virtualUser;

        DB::connection('maria-email-management')->transaction(function () use ($virtualUser): void {
            $virtualUser->forceFill([
                'userPassword' => $this->hand_password,
                'legacy_password' => null,
            ])->save();

            $this->forceFill(['hand_password' => null])->save();
        });
    }

    /**
     * The citoyen goes first: its mail is a foreign key on the login.
     */
    public function deleteWithLogin(): void
    {
        $virtualUser = $this->virtualUser;

        DB::connection('maria-email-management')->transaction(function () use ($virtualUser): void {
            $this->delete();
            $virtualUser->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'last_connection' => 'date',
            'charter_accepted_at' => 'datetime',
            'employeeNumber' => EncryptedPersonalDataCast::class,
            'postalAddress' => EncryptedPersonalDataCast::class,
            'l' => EncryptedPersonalDataCast::class,
            'recovery_email' => EncryptedPersonalDataCast::class,
            'recovery_phone' => EncryptedPersonalDataCast::class,
        ];
    }
}
