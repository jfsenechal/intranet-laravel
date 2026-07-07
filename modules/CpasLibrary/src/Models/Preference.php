<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Connection('maria-cpas-library')]
#[Fillable([
    'name',
    'value',
    'username',
])]
final class Preference extends Model
{
    #[Override]
    public $timestamps = false;
}
