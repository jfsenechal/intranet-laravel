<?php

namespace AcMarche\Mileage\Filament\Resources\Rates\Pages;

use AcMarche\Mileage\Filament\Resources\Rates\RateResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRate extends CreateRecord
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
