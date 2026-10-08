<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Pages;

use AcMarche\MailingList\Filament\Resources\Senders\Schemas\SenderInfolist;
use AcMarche\MailingList\Filament\Resources\Senders\SenderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewSender extends ViewRecord
{
    #[Override]
    protected static string $resource = SenderResource::class;

    public function getTitle(): string
    {
        return $this->record->name.' / '. $this->record->email;
    }

    public function infolist(Schema $schema): Schema
    {
        return SenderInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
