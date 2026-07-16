<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Actions;

use AcMarche\Courrier\Mail\AskAttachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

final class AskAction
{
    /**
     * Ask someone who may read the attachment to share it. Only a user who may
     * not download the attachment can ask for it. The list offers the recipients
     * allowed to read it; the asker picks at least one but may not add anyone else.
     */
    public static function make(): Action
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
                    ->options(
                        fn (IncomingMail $record): array => RecipientRepository::getAttachmentReaderOptions(
                            $record->department
                        )->all()
                    )
                    ->bulkToggleable()
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
                        ->send(
                            new AskAttachment(
                                $record,
                                $asker->fullNameAsString(),
                                (string) $asker->email,
                                $data['note'] ?? null,
                            )
                        );
                }

                Notification::make()
                    ->title('Demande envoyée')
                    ->body(sprintf('Votre demande a été envoyée à %d personne(s).', $readers->count()))
                    ->success()
                    ->send();
            });
    }
}
