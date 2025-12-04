<?php

namespace AcMarche\Mileage\Filament\Resources\TripResource\Pages;

use AcMarche\Mileage\Filament\Resources\TripResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrip extends CreateRecord
{
    protected static string $resource = TripResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un déplacement';
    }
}
