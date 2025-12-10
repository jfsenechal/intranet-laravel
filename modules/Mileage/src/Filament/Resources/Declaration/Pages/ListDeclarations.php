<?php

namespace AcMarche\Mileage\Filament\Resources\Declaration\Pages;

use AcMarche\Mileage\Filament\Resources\Declaration;
use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListDeclarations extends ListRecords
{
    protected static string $resource = DeclarationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Mes déclarations';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle déclaration')
                ->icon('tabler-plus'),
        ];
    }
}
