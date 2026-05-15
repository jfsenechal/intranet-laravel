<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\GroupeFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(GroupeFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'activite_id',
    'jour',
    'heure',
    'lieux',
    'age',
    'prix',
    'description',
    'remarque',
    'user',
])]
final class Groupe extends Model
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
     * @return HasMany<Inscription, $this>
     */
    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }
}
