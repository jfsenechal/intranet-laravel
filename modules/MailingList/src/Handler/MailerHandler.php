<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Handler;

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Jobs\SendEmailJob;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Repositories\OwnerScope;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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

        $perWindow = max(1, (int) config('mailing-list.throttle.per_window'));
        $windowMinutes = max(0, (int) config('mailing-list.throttle.window_minutes'));
        $secondsPerEmail = $windowMinutes > 0
            ? ($windowMinutes * 60) / $perWindow
            : 0;

        $jobs = $pendingRecipients->values()->map(
            fn ($recipient, int $index): SendEmailJob => (new SendEmailJob($email, $recipient))
                ->delay(now()->addSeconds((int) round($index * $secondsPerEmail)))
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

        $body = "Dispatched {$pendingRecipients->count()} emails to the queue.";

        if ($secondsPerEmail > 0) {
            $body .= " Throttled to {$perWindow} every {$windowMinutes} min.";
        }

        Notification::make()
            ->title('Sending started')
            ->body($body)
            ->success()
            ->send();
    }

    /**
     * Every subscribed contact of the given address books, including contacts
     * owned by someone else when the address book is shared or public. Address
     * books the current user may not mail are ignored.
     *
     * @param  array<int|string>  $addressBookIds
     * @return Collection<int, Contact>
     */
    public static function subscribedContactsOfAddressBooks(array $addressBookIds): Collection
    {
        $user = auth()->user();

        if ($user === null || $addressBookIds === []) {
            return new Collection();
        }

        $mailableAddressBookIds = AddressBook::query()
            ->mailableBy($user)
            ->whereIn('id', $addressBookIds)
            ->pluck('id');

        return Contact::query()
            ->withoutGlobalScope(OwnerScope::class)
            ->subscribed()
            ->whereHas('addressBooks', fn (Builder $query) => $query->whereIn('address_books.id', $mailableAddressBookIds))
            ->get();
    }

    public static function syncRecipients(Email|Model $email, array $addressBookIds = [], array $contactIds = []): void
    {
        if ($email->status !== EmailStatus::Draft) {
            return;
        }

        $contacts = collect();

        if ($addressBookIds !== []) {
            $contacts = $contacts->merge(self::subscribedContactsOfAddressBooks($addressBookIds));
        }

        if ($contactIds !== []) {
            $individualContacts = Contact::query()
                ->subscribed()
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
