<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Models;

use AcMarche\GuichetHdv\Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-guichet')]
#[Fillable([
    'number',
    'reason',
    'service',
    'assigned_date',
    'assigned_by',
    'user_add',
    'archive',
    'office_id',
])]
final class Ticket extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }

    protected function casts(): array
    {
        return [
            'archive' => 'boolean',
            'assigned_date' => 'datetime',
        ];
    }
}
