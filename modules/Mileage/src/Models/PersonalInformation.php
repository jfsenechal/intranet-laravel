<?php

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Observers\PersonalInformationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([PersonalInformationObserver::class])]
final class PersonalInformation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $connection = 'maria-mileage';

    protected $fillable = [
        'car_license_plate1',
        'car_license_plate2',
        'postal_code',
        'street',
        'city',
        'college_trip_date',
        'username',
    ];

    protected function casts(): array
    {
        return [

        ];
    }
    //
}
