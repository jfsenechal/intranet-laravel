<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailInfolist;
use AcMarche\Courrier\Mail\AskAttachment;
use AcMarche\Courrier\Mail\ShareIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;
use Override;

final class ViewIncomingMail extends ViewRecord
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    public function getTitle(): string
    {
        return 'Courrier du '.$this->record->mail_date?->translatedFormat('d F Y').' de '.$this->record->sender;
    }

    public function infolist(Schema $schema): Schema
    {
        return IncomingMailInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger la pièce jointe')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->url(fn ($record): ?string => $record->attachments->isNotEmpty()
                    ? route('courrier.attachments.download', $record->attachments->first())
                    : null)
                ->visible(fn ($record): bool => $record->attachments->isNotEmpty()
                    && auth()->user()?->can('download', $record->attachments->first())),
            $this->shareAction(),
            $this->askAction(),
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(IncomingMailResource::getUrl('index')),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    /**
     * Share the courrier and its attachment(s) with recipients. Only a user who
     * may download the attachment can share it.
     */
    private function shareAction(): Action
    {
        return Action::make('share')
            ->label('Partager le courrier')
            ->icon('tabler-share')
            ->color('primary')
            ->visible(fn (IncomingMail $record): bool => $record->attachments->isNotEmpty()
                && auth()->user()?->can('download', $record->attachments->first()))
            ->modalHeading('Partager le courrier')
            ->schema([
                CheckboxList::make('recipients')
                    ->label('Destinataires')
                    ->options(fn (): array => RecipientRepository::getShareOptions()->all())
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(2)
                    ->required(),
                Textarea::make('note')
                    ->label('Message')
                    ->rows(4),
            ])
            ->action(function (array $data, IncomingMail $record): void {
                $recipients = Recipient::query()
                    ->whereIn('id', $data['recipients'])
                    ->whereNotNull('email')
                    ->get();

                foreach ($recipients as $recipient) {
                    Mail::to(new Address($recipient->email, $recipient->full_name))
                        ->send(new ShareIncomingMail($record, $data['note'] ?? null));
                }

                Notification::make()
                    ->title('Courrier partagé')
                    ->body(sprintf('Le courrier a été envoyé à %d destinataire(s).', $recipients->count()))
                    ->success()
                    ->send();
            });
    }

    /**
     * Ask someone who may read the attachment to share it. Only a user who may
     * not download the attachment can ask for it. The pre-filled list holds the
     * recipients allowed to read it; the asker may deselect but not add anyone.
     */
    private function askAction(): Action
    {
        return Action::make('ask')
            ->label('Demander la pièce jointe')
            ->icon('tabler-help')
            ->color(Color::Amber)
            ->visible(fn (IncomingMail $record): bool => $record->attachments->isNotEmpty()
                && ! auth()->user()?->can('download', $record->attachments->first()))
            ->modalHeading('Demander la pièce jointe')
            ->schema([
                CheckboxList::make('readers')
                    ->label('Personnes pouvant accéder à la pièce jointe')
                    ->options(fn (IncomingMail $record): array => RecipientRepository::getAttachmentReaderOptions($record->department)->all())
                    ->default(fn (IncomingMail $record): array => RecipientRepository::getAttachmentReaderOptions($record->department)->keys()->all())
                    ->columns(2)
                    ->required(),
                Textarea::make('note')
                    ->label('Message')
                    ->rows(4),
            ])
            ->action(function (array $data, IncomingMail $record): void {
                $asker = auth()->user();

                if (! $asker instanceof User) {
                    return;
                }

                $readers = Recipient::query()
                    ->whereIn('id', $data['readers'])
                    ->whereNotNull('email')
                    ->get();

                foreach ($readers as $reader) {
                    Mail::to(new Address($reader->email, $reader->full_name))
                        ->send(new AskAttachment(
                            $record,
                            $asker->fullNameAsString(),
                            (string) $asker->email,
                            $data['note'] ?? null,
                        ));
                }

                Notification::make()
                    ->title('Demande envoyée')
                    ->body(sprintf('Votre demande a été envoyée à %d personne(s).', $readers->count()))
                    ->success()
                    ->send();
            });
    }
}
