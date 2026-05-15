<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\SportsActivities\Database\Factories\SportifFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(SportifFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'nom',
    'prenom',
    'ne_le',
    'rue',
    'code_postal',
    'localite',
    'telephone',
    'gsm',
    'email',
    'remarque',
    'user',
])]
final class Sportif extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ne_le' => 'date',
    ];

    /**
     * @return HasMany<Inscription, $this>
     */
    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }
}
