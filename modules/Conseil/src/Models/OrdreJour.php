<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\OrdreJourFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(OrdreJourFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'nom',
    'date_ordre',
    'date_fin_diffusion',
    'file_name',
])]
final class OrdreJour extends Model
{
    use HasFactory;

    protected $table = 'ordre_jour';

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_ordre' => 'datetime',
        'date_fin_diffusion' => 'date',
    ];
}
