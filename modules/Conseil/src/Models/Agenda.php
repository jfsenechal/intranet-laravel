<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\AgendaFactory;
use AcMarche\Conseil\Observers\AgendaObserver;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(AgendaFactory::class)]
#[ObservedBy(AgendaObserver::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'name',
    'agenda_date',
    'file_name',
])]
final class Agenda extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'agenda_date' => 'datetime',
    ];
}
