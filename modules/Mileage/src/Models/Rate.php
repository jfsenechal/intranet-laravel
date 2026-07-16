<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Database\Factories\RateFactory;
use AcMarche\Mileage\Observers\RateObserver;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(RateFactory::class)]
#[ObservedBy([RateObserver::class])]
#[Connection('maria-mileage')]
#[Fillable([
    'amount',
    'omnium',
    'start_date',
    'end_date',
])]
final class Rate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'omnium' => 'decimal:4',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
