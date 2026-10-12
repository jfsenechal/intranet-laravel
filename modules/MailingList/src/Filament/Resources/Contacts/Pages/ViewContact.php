<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts\Pages;

use AcMarche\MailingList\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewContact extends ViewRecord
{
    #[Override]
    protected static string $resource = ContactResource::class;

    public function getTitle(): string
    {
        return mb_trim($this->record->first_name.' '.$this->record->last_name) ?: $this->record->email;
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
