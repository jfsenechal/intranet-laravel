<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditTicket extends EditRecord
{
    #[Override]
    protected static string $resource = TicketResource::class;

    public function getTitle(): string
    {
        return 'Ticket #'.$this->record->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
