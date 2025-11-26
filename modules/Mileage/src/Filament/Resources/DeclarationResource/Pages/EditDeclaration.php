<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Pages;

use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeclaration extends EditRecord
{
    protected static string $resource = DeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
