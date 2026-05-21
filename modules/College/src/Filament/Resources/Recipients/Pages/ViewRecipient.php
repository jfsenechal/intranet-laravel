<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use AcMarche\College\Filament\Resources\Recipients\Schemas\RecipientInfoList;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
        return RecipientInfoList::configure($schema);
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
