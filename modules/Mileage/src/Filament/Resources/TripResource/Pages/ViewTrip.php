<?php

namespace AcMarche\Mileage\Filament\Resources\TripResource\Pages;

use AcMarche\Mileage\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewTrip extends ViewRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist2(Schema $schema): Schema
    {
        return NewsInfolist::configure($schema);
    }

}
