<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Actions;

use AcMarche\Agent\Mail\ProfileChangesMail;
use AcMarche\Agent\Models\Profile;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

final class SendProfileChangesAction
{
    public static function make(): Action
    {
        return Action::make('sendProfileChanges')
            ->label('Envoyer les changements au service informatique')
            ->icon(Heroicon::PaperAirplane)
            ->color('warning')
            ->visible(fn (Profile $record): bool => Auth::user()?->can('update', $record) === true)
            ->modalHeading(fn (Profile $record): string => 'Changements sur le profil de '.$record->fullName())
            ->modalDescription('Prévenez le service informatique des changements apportés à ce profil.')
            ->modalSubmitActionLabel('Envoyer')
            ->schema([
                Textarea::make('notes')
                    ->label('Changements')
                    ->placeholder('Par exemple, nouveau bureau, changement de téléphone, accès à un dossier...')
                    ->required()
                    ->rows(5),
            ])
            ->action(function (array $data, Profile $record): void {
                $recipient = config('agent.informatique_email');

                if (empty($recipient)) {
                    Notification::make()
                        ->title('Adresse informatique non configurée')
                        ->body('L\'adresse du service informatique n\'est pas configurée.')
                        ->danger()
                        ->send();

                    return;
                }

                $mail = Mail::to($recipient);

                if ($sender = Auth::user()?->email) {
                    $mail->cc($sender);
                }

                $mail->send(new ProfileChangesMail($record, $data['notes']));

                Notification::make()
                    ->title('Les changements ont bien été envoyés au service informatique')
                    ->success()
                    ->send();
            });
    }
}
