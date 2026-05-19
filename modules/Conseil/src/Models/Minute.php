<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\MinuteFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(MinuteFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'name',
    'meeting_date',
    'file_name',
])]
final class Minute extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'meeting_date' => 'date',
    ];
}
