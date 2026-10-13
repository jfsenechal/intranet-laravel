<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Pages;

use AcMarche\MailingList\Filament\Resources\Emails\EmailResource;
use AcMarche\MailingList\Handler\MailerHandler;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditEmail extends EditRecord
{
    #[Override]
    protected static string $resource = EmailResource::class;

    protected function afterSave(): void
    {
        MailerHandler::syncRecipients(
            $this->record,
            $this->data['address_book_ids'] ?? [],
            $this->data['contact_ids'] ?? [],
        );
    }
}
