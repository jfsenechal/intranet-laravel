<?php

namespace AcMarche\Mileage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    use HasFactory;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'car_license_plate1',
        'car_license_plate2',
        'postal_code',
        'street',
        'city',
        'college_trip_date',
    ];

    protected function casts(): array
    {
        return [

        ];
    }
    //
}
