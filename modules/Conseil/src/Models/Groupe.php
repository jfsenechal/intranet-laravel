<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\GroupeFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(GroupeFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'nom',
])]
final class Groupe extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsToMany<Destinataire, $this>
     */
    public function destinataires(): BelongsToMany
    {
        return $this->belongsToMany(Destinataire::class, 'groupe_destinataire');
    }

    /**
     * @return HasMany<PieceJointe, $this>
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointe::class);
    }
}
