<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-hrm')]
#[Fillable([
    'last_name',
    'first_name',
    'email_1',
    'phone_1',
    'email_2',
    'phone_2',
    'description',
])]
final class Contact extends Model {}
