<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts\Pages;

use AcMarche\MailingList\Filament\Resources\Contacts\ContactResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateContact extends CreateRecord
{
    #[Override]
    protected static string $resource = ContactResource::class;

    #[Override]
    protected static ?string $title = 'Nouveau contact';
}
