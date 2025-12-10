<?php

namespace AcMarche\Mileage\Models;

use Database\Factories\RateFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(RateFactory::class)]
class Rate extends Model
{
    use HasFactory;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'amount',
        'omnium',
        'start_date',
        'end_date',
    ];

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
