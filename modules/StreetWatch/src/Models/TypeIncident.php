<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Models;

use AcMarche\StreetWatch\Database\Factories\TypeIncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[UseFactory(TypeIncidentFactory::class)]
#[Connection('maria-street-watch')]
#[Table(name: 'types')]
#[Fillable([
    'name',
])]
final class TypeIncident extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return HasMany<Incident, $this>
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'typeIncident_id');
    }
}
