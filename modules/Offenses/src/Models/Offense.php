<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
    use HasUserAdd;

    public function offender(): BelongsTo
    {
        return $this->belongsTo(Offender::class);
    }

    public function offenseAct(): BelongsTo
    {
        return $this->belongsTo(OffenseAct::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::deleted(function (self $offense): void {
            if (filled($offense->file_name)) {
                Storage::disk(config('offenses.storage.disk'))->delete($offense->file_name);
            }
        });
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
