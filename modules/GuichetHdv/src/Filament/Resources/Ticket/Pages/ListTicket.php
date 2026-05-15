<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListTicket extends ListRecords
{
    #[Override]
    protected static string $resource = TicketResource::class;

    public function getTitle(): string
    {
        return 'Tickets ('.$this->getAllTableRecordsCount().')';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un ticket')
                ->icon('tabler-plus'),
        ];
    }
}
