<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Senders\Pages;

use AcMarche\Courrier\Filament\Resources\Senders\SenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListSenders extends ListRecords
{
    #[Override]
    protected static string $resource = SenderResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' expéditeurs';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un expéditeur')
                ->icon('tabler-plus'),
        ];
    }
}
