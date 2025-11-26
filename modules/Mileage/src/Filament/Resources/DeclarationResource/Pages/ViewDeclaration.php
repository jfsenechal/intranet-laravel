<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Pages;

use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeclaration extends ViewRecord
{
    protected static string $resource = DeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
