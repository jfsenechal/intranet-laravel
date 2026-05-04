<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-offenses')]
#[Fillable([
    'offender_id',
    'offense_act_id',
    'decision_date',
    'fine_amount',
    'mediation',
    'prosecutor_opinion',
    'file_name',
    'user_add',
])]
final class Offense extends Model
{
    public function offender(): BelongsTo
    {
        return $this->belongsTo(Offender::class);
    }

    public function offenseAct(): BelongsTo
    {
        return $this->belongsTo(OffenseAct::class);
    }

    protected function casts(): array
    {
        return [
            'decision_date' => 'date',
            'fine_amount' => 'float',
            'mediation' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
