<?php

namespace AcMarche\Mileage\Filament\Resources\RateResource\Pages;

use AcMarche\Mileage\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRates extends ListRecords
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un tarif')
                ->icon('tabler-plus'),
        ];
    }
}
