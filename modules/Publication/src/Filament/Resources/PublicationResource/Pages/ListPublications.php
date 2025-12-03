<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Pages;

use AcMarche\Publication\Filament\Resources\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' publications';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une publication')
                ->icon('tabler-plus'),
        ];
    }
}
