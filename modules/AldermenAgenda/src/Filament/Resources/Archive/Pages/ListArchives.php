<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Archive\ArchiveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListArchives extends ListRecords
{
    #[Override]
    protected static string $resource = ArchiveResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' archives';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une archive')
                ->icon('tabler-plus'),
        ];
    }
}
