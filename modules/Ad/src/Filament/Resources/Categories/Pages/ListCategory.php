<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\Categories\Pages;

use AcMarche\Ad\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListCategory extends ListRecords
{
    #[Override]
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' catégories';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une catégorie')
                ->icon('tabler-plus'),
        ];
    }
}
