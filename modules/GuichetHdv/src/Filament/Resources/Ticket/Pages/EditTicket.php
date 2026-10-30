<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Ticket\TicketResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
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
                ->icon(Heroicon::Eye),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
