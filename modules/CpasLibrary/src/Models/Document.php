<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-cpas-library')]
#[Table(name: 'document')]
#[Fillable([
    'name',
])]
final class Document extends Model
{
    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';
}
