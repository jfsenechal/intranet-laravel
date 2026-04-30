<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Models;

use AcMarche\Mileage\Database\Factories\PersonalInformationFactory;
use AcMarche\Mileage\Observers\PersonalInformationObserver;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

#[UseFactory(PersonalInformationFactory::class)]
#[ObservedBy([PersonalInformationObserver::class])]
#[Connection('maria-mileage')]
#[Fillable([
    'car_license_plate1',
    'car_license_plate2',
    'postal_code',
    'street',
    'city',
    'college_trip_date',
    'username',
    'iban',
    'omnium',
])]
final class PersonalInformation extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'omnium' => 'boolean',
        ];
    }
    //
}
