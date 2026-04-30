<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Database\Factories\RateFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(RateFactory::class)]
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
            'amount' => 'decimal:2',
            'omnium' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
