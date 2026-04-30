<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Pages;

use AcMarche\MailingList\Filament\Resources\Senders\SenderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditSender extends EditRecord
{
    #[Override]
    protected static string $resource = SenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
