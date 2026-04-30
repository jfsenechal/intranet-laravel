<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDeclarations extends ListRecords
{
    #[Override]
    protected static string $resource = DeclarationResource::class;

    public function getTitle(): string
    {
        return 'Mes déclarations';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle déclaration')
                ->icon('tabler-plus'),
        ];
    }
}
