<?php

namespace AcMarche\Mileage\Filament\Resources\RateResource\Pages;

use AcMarche\Mileage\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRate extends EditRecord
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
