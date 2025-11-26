<?php

namespace AcMarche\Mileage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'montant',
        'omnium',
        'date_debut',
        'date_fin',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'omnium' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }
}
