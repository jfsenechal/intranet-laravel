<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Database\Factories\VirtualUserFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A mailbox login read by Dovecot.
 *
 * The username is the full email address. The password hashes are hidden so they are never
 * serialized. Models are globally unguarded (see config/essentials.php), so leaving them out
 * of the fillable list does not stop them being mass assigned: never fill this from user input.
 */
#[Connection('maria-email-management')]
#[Table('virtual_users')]
#[Fillable([
    'username',
    'quota_bytes',
    'active',
])]
#[Hidden([
    'userPassword',
    'legacy_password',
])]
#[UseFactory(VirtualUserFactory::class)]
final class VirtualUser extends Model
{
    use HasFactory;

    /**
     * Same parameters as gestmail, which writes this column too. The memory cost is below
     * PHP's default because Dovecot verifies the hash on every login.
     *
     * @var array{memory_cost: int, time_cost: int, threads: int}
     */
    private const array ARGON2ID_OPTIONS = [
        'memory_cost' => 32768,
        'time_cost' => 4,
        'threads' => 1,
    ];

    /**
     * Stores the password in the format the Roundcube password plugin writes, which Dovecot
     * reads as its ARGON2ID scheme. Dovecot prefers userPassword over legacy_password, so
     * the legacy hash is cleared and can no longer be used.
     */
    public function changePassword(string $plainPassword): void
    {
        $this->forceFill([
            'userPassword' => '{ARGON2ID}'.password_hash($plainPassword, PASSWORD_ARGON2ID, self::ARGON2ID_OPTIONS),
            'legacy_password' => null,
        ])->save();
    }

    protected function casts(): array
    {
        return [
            'quota_bytes' => 'integer',
            'active' => 'boolean',
        ];
    }
}
