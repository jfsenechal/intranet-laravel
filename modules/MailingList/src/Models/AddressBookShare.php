<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Models;

use AcMarche\MailingList\Enums\SharePermission;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-mailing-list')]
#[Fillable([
    'address_book_id',
    'username',
    'permission',
])]
final class AddressBookShare extends Model
{
    use BelongsToUserTrait;
    use HasFactory;

    /**
     * @return BelongsTo<AddressBook, $this>
     */
    public function addressBook(): BelongsTo
    {
        return $this->belongsTo(AddressBook::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permission' => SharePermission::class,
        ];
    }
}
