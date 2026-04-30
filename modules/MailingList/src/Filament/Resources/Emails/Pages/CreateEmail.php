<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Pages;

use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Filament\Resources\Emails\EmailResource;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateEmail extends CreateRecord
{
    #[Override]
    protected static string $resource = EmailResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Email $email */
        $email = self::getModel()::create($data);

        $this->createRecipients($email);

        return $email;
    }

    private function createRecipients(Email $email): void
    {
        $contacts = collect();

        $addressBookIds = $this->data['address_book_ids'] ?? [];
        if (! empty($addressBookIds)) {
            $addressBookContacts = Contact::query()
                ->whereHas('addressBooks', fn ($query) => $query->whereIn('address_books.id', $addressBookIds))
                ->get();
            $contacts = $contacts->merge($addressBookContacts);
        }

        $contactIds = $this->data['contact_ids'] ?? [];
        if (! empty($contactIds)) {
            $individualContacts = Contact::query()
                ->whereIn('id', $contactIds)
                ->get();
            $contacts = $contacts->merge($individualContacts);
        }

        $contacts = $contacts->unique('id');

        foreach ($contacts as $contact) {
            $email->recipients()->create([
                'contact_id' => $contact->id,
                'email_address' => $contact->email,
                'name' => mb_trim("{$contact->first_name} {$contact->last_name}"),
                'status' => RecipientStatus::Pending,
            ]);
        }

        $email->update(['total_count' => $contacts->count()]);
    }
}
