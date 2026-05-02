<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Recipient\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Recipient\RecipientResource;
use AcMarche\AgendaEchevin\Filament\Resources\Recipient\Schemas\RecipientInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewRecipient extends ViewRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return RecipientInfolist::configure($schema);
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
