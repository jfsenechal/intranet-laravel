<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Pages;

use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeclarations extends ListRecords
{
    protected static string $resource = DeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle déclaration')
                ->icon('tabler-plus'),
        ];
    }
}
