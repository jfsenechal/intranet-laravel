<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas\TicketInfolist;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewTicket extends ViewRecord
{
    #[Override]
    protected static string $resource = TicketResource::class;

    public function getTitle(): string
    {
        return 'Ticket #'.$this->record->number;
    }

    public function infolist(Schema $schema): Schema
    {
        return TicketInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
