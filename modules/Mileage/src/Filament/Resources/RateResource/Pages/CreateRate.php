<?php

namespace AcMarche\Mileage\Filament\Resources\RateResource\Pages;

use AcMarche\Mileage\Filament\Resources\RateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRate extends CreateRecord
{
    protected static string $resource = RateResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un tarif';
    }
}
