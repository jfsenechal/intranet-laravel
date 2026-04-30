<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Pages;

use AcMarche\MailingList\Filament\Resources\Senders\SenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListSenders extends ListRecords
{
    #[Override]
    protected static string $resource = SenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel expediteur')
                ->icon(Heroicon::Plus),
        ];
    }
}
