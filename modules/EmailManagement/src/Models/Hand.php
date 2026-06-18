<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use AcMarche\EmailManagement\Database\Factories\HandFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-email-management')]
#[Fillable([
    'uid',
    'email',
    'password',
])]
#[UseFactory(HandFactory::class)]
final class Hand extends Model
{
    use HasFactory;
}
