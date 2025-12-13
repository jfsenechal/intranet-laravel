<?php

namespace AcMarche\Document\Filament\Resources\Documents\Pages;

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un document')
                ->icon('tabler-plus'),
        ];
    }
}
