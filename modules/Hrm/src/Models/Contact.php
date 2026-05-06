<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[Table(name: 'hrm_contacts')]
#[UseFactory(ContactFactory::class)]
final class Contact extends Model
{
    use HasFactory;
}
