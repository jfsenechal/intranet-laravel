<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\PieceJointeFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(PieceJointeFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'groupe_id',
    'nom',
    'description',
])]
final class PieceJointe extends Model
{
    use HasFactory;

    protected $table = 'PieceJointe';

    public $timestamps = false;

    /**
     * @return BelongsTo<Groupe, $this>
     */
    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class);
    }
}
