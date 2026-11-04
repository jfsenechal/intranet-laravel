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

    protected function casts(): array
    {
        return [
            'quota_bytes' => 'integer',
            'active' => 'boolean',
        ];
    }
}
