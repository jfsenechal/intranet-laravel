<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Pages;

use AcMarche\Publication\Filament\Resources\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
