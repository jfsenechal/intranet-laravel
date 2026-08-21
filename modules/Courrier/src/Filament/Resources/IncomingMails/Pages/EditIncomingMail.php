<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Pages\DraftIncomingMails;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Jobs\IndexIncomingMailJob;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Sender;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Courrier\Search\SuggestsMailRouting;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditIncomingMail extends EditRecord
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    /** @var array<int> */
    private array $primaryServices = [];

    /** @var array<int> */
    private array $secondaryServices = [];

    /** @var array<int> */
    private array $primaryRecipients = [];

    /** @var array<int> */
    private array $secondaryRecipients = [];

    private bool $saveSender = false;

    /**
     * Whether the running save was started by the "Valider et suivant" button,
     * read back in afterSave() — which only runs once the form validated.
     */
    private bool $validatingDraft = false;

    public function getTitle(): string
    {
        return $this->record->is_draft ? 'Vérifier le brouillon' : 'Modifier le courrier';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (! $this->record->is_draft) {
            return null;
        }

        return 'Ces informations ont été proposées par l\'IA et n\'ont pas encore été relues. '
            .'Le courrier reste invisible et n\'est pas notifié tant qu\'il n\'est pas validé.';
    }

    public function validateDraft(): void
    {
        $this->validatingDraft = true;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
        } finally {
            $this->validatingDraft = false;
        }

        // save() swallows a validation failure and returns, leaving the errors
        // on the form; the flag having survived is the only signal that the
        // draft was not published — a mail the model left without a reference
        // number stops here rather than going out unread.
        if ($this->record->is_draft) {
            return;
        }

        Notification::make()
            ->title('Courrier validé')
            ->body("Le courrier #{$this->record->reference_number} est désormais visible.")
            ->success()
            ->send();

        $this->redirect($this->urlOfNextDraft());
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->visible(fn (): bool => ! $this->record->is_draft),
            DeleteAction::make(),
        ];
    }

    /**
     * A draft is validated rather than merely saved: the button clears the draft
     * flag — which publishes the mail to the listing, the notifications and the
     * index — and opens the next draft, so a batch is walked through without
     * going back to a listing that does not show them.
     */
    protected function getFormActions(): array
    {
        if (! $this->record->is_draft) {
            return parent::getFormActions();
        }

        return [
            Action::make('validateDraft')
                // Not a form submit: the form's own submit handler is save().
                // A Livewire click still flushes the pending field updates, so
                // the state save() validates is the one on screen.
                ->action('validateDraft')
                ->label('Valider et suivant')
                ->icon('tabler-check')
                ->color(Color::Indigo),
            ...parent::getFormActions(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['primary_services'] = $this->record->services()->wherePivot('is_primary', true)->pluck(
            'courrier_services.id'
        )->toArray();
        $data['secondary_services'] = $this->record->services()->wherePivot('is_primary', false)->pluck(
            'courrier_services.id'
        )->toArray();
        $data['primary_recipients'] = $this->record->recipients()->wherePivot('is_primary', true)->pluck(
            'recipients.id'
        )->toArray();
        $data['secondary_recipients'] = $this->record->recipients()->wherePivot('is_primary', false)->pluck(
            'recipients.id'
        )->toArray();

        return $this->withRetrievedRouting($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->primaryServices = $data['primary_services'] ?? [];
        $this->secondaryServices = $data['secondary_services'] ?? [];
        $this->primaryRecipients = $data['primary_recipients'] ?? [];
        $this->secondaryRecipients = $data['secondary_recipients'] ?? [];
        $this->saveSender = (bool) ($data['save_sender'] ?? false);

        unset(
            $data['attachment_file'],
            $data['primary_services'],
            $data['secondary_services'],
            $data['primary_recipients'],
            $data['secondary_recipients'],
            $data['save_sender'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        // Save sender to senders table if checkbox was checked. The sender
        // inherits the mail's department so it stays visible under the
        // department scope.
        if ($this->saveSender && $this->record->sender) {
            Sender::firstOrCreate(
                ['name' => $this->record->sender],
                ['department' => $this->record->department],
            );
        }

        // Sync services
        $services = [];
        foreach ($this->primaryServices as $serviceId) {
            $services[$serviceId] = ['is_primary' => true];
        }
        foreach ($this->secondaryServices as $serviceId) {
            $services[$serviceId] = ['is_primary' => false];
        }
        $this->record->services()->sync($services);

        // Sync recipients
        $recipients = [];
        foreach ($this->primaryRecipients as $recipientId) {
            $recipients[$recipientId] = ['is_primary' => true];
        }
        foreach ($this->secondaryRecipients as $recipientId) {
            $recipients[$recipientId] = ['is_primary' => false];
        }
        $this->record->recipients()->sync($recipients);

        // Publishing the draft is the last thing the save does, so it only
        // happens once the form has validated and the relations are in place.
        // The update re-indexes the mail, which the draft was kept out of.
        if ($this->validatingDraft) {
            $this->record->update(['is_draft' => false]);

            return;
        }

        // Pivot writes fire no model event, so nothing has re-indexed the mail
        // when only its recipients or services changed.
        IndexIncomingMailJob::dispatch($this->record->id)->afterCommit();
    }

    /**
     * Route a draft that came back unrouted, from the courriers that resemble
     * it.
     *
     * Where a courrier goes is not written on the paper, so it is retrieved
     * from the mail already encoded. The batch normally fills this in when it
     * creates the draft; this catches the ones it could not — a draft encoded
     * before the retrieval existed, or one whose text only reached the index
     * afterwards. Only a field still empty is filled, and never on a courrier a
     * human has validated: there, a retrieval could only offer to undo a
     * routing someone chose deliberately.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withRetrievedRouting(array $data): array
    {
        if (! $this->record->is_draft || blank($this->record->content)) {
            return $data;
        }

        if ($data['primary_recipients'] !== [] && $data['primary_services'] !== []) {
            return $data;
        }

        $suggestion = app(SuggestsMailRouting::class)->suggestFor($this->record);

        if ($data['primary_recipients'] === []) {
            $data['primary_recipients'] = $suggestion->topRecipientIds();
        }

        if ($data['primary_services'] === []) {
            $data['primary_services'] = $suggestion->topServiceIds();
        }

        return $data;
    }

    /**
     * The next draft to verify, or the drafts page — which then shows its empty
     * state — when the queue is done.
     */
    private function urlOfNextDraft(): string
    {
        $next = IncomingMailRepository::nextDraftAfter($this->record);

        return $next instanceof IncomingMail
            ? IncomingMailResource::getUrl('edit', ['record' => $next])
            : DraftIncomingMails::getUrl();
    }
}
