<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\ActiviteFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ActiviteFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'nom',
    'description',
    'user',
    'archive',
])]
final class Activite extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'archive' => 'boolean',
    ];

    /**
     * @return HasMany<Groupe, $this>
     */
    public function groupes(): HasMany
    {
        return $this->hasMany(Groupe::class);
    }

    /**
     * @return HasMany<Inscription, $this>
     */
    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }
}
