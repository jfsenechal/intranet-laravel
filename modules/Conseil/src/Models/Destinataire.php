<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\DestinataireFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(DestinataireFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'nom',
    'prenom',
    'email',
])]
final class Destinataire extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsToMany<Groupe, $this>
     */
    public function groupes(): BelongsToMany
    {
        return $this->belongsToMany(Groupe::class, 'groupe_destinataire');
    }
}
