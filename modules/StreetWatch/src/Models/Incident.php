<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Models;

use AcMarche\StreetWatch\Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(IncidentFactory::class)]
#[Connection('maria-street-watch')]
#[Table(name: 'incidents')]
#[Fillable([
    'place',
    'object',
    'description',
    'response',
    'user_add',
    'occurred_date',
    'requestBy_id',
    'typeIncident_id',
])]
final class Incident extends Model
{
    use HasFactory;

    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';

    /**
     * @return BelongsTo<RequestBy, $this>
     */
    public function requestBy(): BelongsTo
    {
        return $this->belongsTo(RequestBy::class, 'requestBy_id');
    }

    /**
     * @return BelongsTo<TypeIncident, $this>
     */
    public function typeIncident(): BelongsTo
    {
        return $this->belongsTo(TypeIncident::class, 'typeIncident_id');
    }

    protected static function booted(): void
    {
        self::creating(function (self $incident): void {
            if (empty($incident->user_add) && auth()->check()) {
                $incident->user_add = (string) (auth()->user()->username ?? auth()->user()->email);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_date' => 'datetime',
            'createdAt' => 'datetime',
            'updatedAt' => 'datetime',
        ];
    }
}
