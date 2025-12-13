<?php

namespace AcMarche\Publication\Filament\Resources\Categories\Pages;

use AcMarche\Publication\Filament\Resources\Categories\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' catégories';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une catégorie')
                ->icon('tabler-plus'),
        ];
    }
}
