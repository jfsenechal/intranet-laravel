<?php

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListDeclarations extends ListRecords
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
