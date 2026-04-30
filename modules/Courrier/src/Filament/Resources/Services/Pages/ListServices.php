<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Pages;

use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
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

    public function getSubheading(): string
    {
        return 'Services ou groupes de destinataires';
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
