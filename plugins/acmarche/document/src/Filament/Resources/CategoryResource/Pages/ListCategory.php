<?php

namespace AcMarche\Document\Filament\Resources\CategoryResource\Pages;

use AcMarche\Document\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListCategory extends ListRecords
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
