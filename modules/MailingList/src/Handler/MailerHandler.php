<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Handler;

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Jobs\SendEmailJob;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

final class MailerHandler
{
    public static function sendEmail(Email|Model $email): void
    {
        if ($email->recipients()->count() === 0) {
            Notification::make()
                ->title('No recipients')
                ->body('Add at least one address book or contact before sending.')
                ->danger()
                ->send();

            return;
        }

        $email->load('sender');

        $email->recipients()
            ->where('status', '!=', RecipientStatus::Sent)
            ->update([
                'status' => RecipientStatus::Pending,
                'error' => null,
            ]);

        $pendingRecipients = $email->recipients()
            ->where('status', RecipientStatus::Pending)
            ->get();

        $jobs = $pendingRecipients->map(
            fn ($recipient): SendEmailJob => new SendEmailJob($email, $recipient)
        )->all();

        $batch = Bus::batch($jobs)
            ->then(function () use ($email): void {
                $email->update(['status' => EmailStatus::Sent]);
            })
            ->catch(function () use ($email): void {
                $email->update(['status' => EmailStatus::Failed]);
            })
            ->allowFailures()
            ->dispatch();

        $email->update([
            'status' => EmailStatus::Sending,
            'batch_id' => $batch->id,
        ]);

        Notification::make()
            ->title('Sending started')
            ->body("Dispatched {$pendingRecipients->count()} emails to the queue.")
            ->success()
            ->send();
    }

    public static function syncRecipients(Email|Model $email, array $addressBookIds = [], array $contactIds = []): void
    {
        if ($email->status !== EmailStatus::Draft) {
            return;
        }

        $contacts = collect();

        if ($addressBookIds !== []) {
            $addressBookContacts = Contact::query()
                ->whereHas('addressBooks', fn ($query) => $query->whereIn('address_books.id', $addressBookIds))
                ->get();
            $contacts = $contacts->merge($addressBookContacts);
        }

        if ($contactIds !== []) {
            $individualContacts = Contact::query()
                ->whereIn('id', $contactIds)
                ->get();
            $contacts = $contacts->merge($individualContacts);
        }

        $contacts = $contacts->unique('id');

        $email->recipients()->delete();

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
