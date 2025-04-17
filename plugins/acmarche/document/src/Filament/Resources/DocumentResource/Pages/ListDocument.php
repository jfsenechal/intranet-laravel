<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Pages;

use AcMarche\Document\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListDocument extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' documents';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un document')
                ->icon('tabler-plus'),
        ];
    }

}
