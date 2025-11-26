<?php

namespace AcMarche\Mileage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $connection = 'maria-mileage';

    protected $table = 'rates';

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
