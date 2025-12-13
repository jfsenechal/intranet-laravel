<?php

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Modification d'un déplacement";
    }
}
