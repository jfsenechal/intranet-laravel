<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service\Pages;

use AcMarche\Pst\Filament\Resources\Service\ServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListServices extends ListRecords
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' services';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un service')
                ->icon('tabler-plus'),
        ];
    }
}
