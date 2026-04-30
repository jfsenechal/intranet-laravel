<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Pages;

use AcMarche\MailingList\Filament\Resources\Emails\EmailResource;
use AcMarche\MailingList\Handler\MailerHandler;
use AcMarche\MailingList\Models\Email;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditEmail extends EditRecord
{
    #[Override]
    protected static string $resource = EmailResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['address_book_ids'], $data['contact_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        MailerHandler::syncRecipients($this->record, $this->data['address_book_ids'], $this->data['contact_ids']);
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        /** @var Email $email */
        $email = $this->record;

        $addressBookIds = $email->recipients()
            ->whereNotNull('contact_id')
            ->with('contact.addressBooks')
            ->get()
            ->pluck('contact.addressBooks')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $contactIds = $email->recipients()
            ->whereNotNull('contact_id')
            ->pluck('contact_id')
            ->all();

        $this->data['address_book_ids'] = $addressBookIds;
        $this->data['contact_ids'] = $contactIds;
    }
}
