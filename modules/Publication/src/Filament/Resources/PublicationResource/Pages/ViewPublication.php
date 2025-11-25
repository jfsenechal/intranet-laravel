<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Pages;

use AcMarche\Publication\Filament\Resources\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewPublication extends ViewRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
