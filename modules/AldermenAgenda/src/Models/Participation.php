<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-aldermen-agenda')]
#[Fillable(['event_id', 'recipient_id', 'response'])]
final class Participation extends Model
{
    use HasFactory;

    protected $table = 'agenda_echevin_participations';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    protected function casts(): array
    {
        return [
            'response' => 'boolean',
        ];
    }
}
