<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Publications\Pages;

use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListPublications extends ListRecords
{
    #[Override]
    protected static string $resource = PublicationResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' publications';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une publication')
                ->icon('tabler-plus'),
        ];
    }
}
