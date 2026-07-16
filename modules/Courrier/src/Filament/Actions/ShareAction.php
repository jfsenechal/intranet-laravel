<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Actions;

use AcMarche\Courrier\Mail\ShareIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

final class ShareAction
{
    /**
     * Share the courrier and its attachment(s) with recipients. Only a user who
     * may download the attachment can share it.
     */
    public static function make(): Action
    {
        return Action::make('share')
            ->label('Partager le courrier')
            ->icon('tabler-share')
            ->color('primary')
            ->visible(fn (IncomingMail $record): bool => $record->attachments->isNotEmpty()
                && auth()->user()?->can('download', $record->attachments->first()))
            ->modalHeading('Partager le courrier')
            ->modalDescription('Le courrier partira en pièce jointe.')
            ->schema([
                Select::make('recipients')
                    ->label('Destinataires')
                    ->multiple()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search): array => RecipientRepository::searchShareOptions($search)->all(),
                    )
                    ->getOptionLabelsUsing(
                        fn (array $values): array => RecipientRepository::getShareLabels($values)->all(),
                    )
                    ->searchPrompt('Rechercher un destinataire par nom ou email')
                    ->noSearchResultsMessage('Aucun destinataire trouvé.')
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
}
