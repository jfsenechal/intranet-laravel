<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\InscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(InscriptionFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'activite_id',
    'groupe_id',
    'sportif_id',
    'prix',
    'remarque',
    'user',
])]
final class Inscription extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'prix' => 'float',
    ];

    /**
     * @return BelongsTo<Activite, $this>
     */
    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    /**
     * @return BelongsTo<Groupe, $this>
     */
    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class);
    }

    /**
     * @return BelongsTo<Sportif, $this>
     */
    public function sportif(): BelongsTo
    {
        return $this->belongsTo(Sportif::class);
    }
}
