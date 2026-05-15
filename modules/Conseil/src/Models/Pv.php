<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\PvFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(PvFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'nom',
    'date_pv',
    'file_name',
])]
final class Pv extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_pv' => 'date',
    ];
}
