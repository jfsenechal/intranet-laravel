<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Pages;

use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateAddressBook extends CreateRecord
{
    #[Override]
    protected static string $resource = AddressBookResource::class;

    public function getTitle(): string
    {
        return 'Nouveau carnet';
    }
}
