<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts\Pages;

use AcMarche\MailingList\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditContact extends EditRecord
{
    #[Override]
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
