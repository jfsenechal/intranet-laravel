<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Pages;

use AcMarche\MailingList\Filament\Actions\ImportContactAction;
use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListAddressBooks extends ListRecords
{
    #[Override]
    protected static string $resource = AddressBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportContactAction::make(),
            CreateAction::make()
                ->label('Nouveau carnet')
                ->icon(Heroicon::Plus),
        ];
    }
}
