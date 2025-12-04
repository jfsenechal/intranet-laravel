<?php

namespace AcMarche\Mileage\Filament\Resources\TripResource\Pages;

use AcMarche\Mileage\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListTrips extends ListRecords
{
    protected static string $resource = TripResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Liste des déplacements';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau déplacement')
                ->icon('tabler-plus'),
        ];
    }
}
