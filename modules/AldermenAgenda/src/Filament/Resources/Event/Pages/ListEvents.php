<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Event\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEvents extends ListRecords
{
    #[Override]
    protected static string $resource = EventResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' événements';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un événement')
                ->icon('tabler-plus'),
        ];
    }
}
