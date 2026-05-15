<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Office\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Office\OfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOffice extends ListRecords
{
    #[Override]
    protected static string $resource = OfficeResource::class;

    public function getTitle(): string
    {
        return 'Guichets ('.$this->getAllTableRecordsCount().')';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un guichet')
                ->icon('tabler-plus'),
        ];
    }
}
