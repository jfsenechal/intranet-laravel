<?php

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Models\Trip;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTrip extends ViewRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit')
                ->disabled(fn(Trip $trip) => $trip->isDeclared())
                ->tooltip(
                    fn(Trip $trip) => $trip->isDeclared() ? 'Ce déplacement est déjà lié à une déclaration' : null
                ),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Détails du déplacement '.$this->record->id;
    }

}
