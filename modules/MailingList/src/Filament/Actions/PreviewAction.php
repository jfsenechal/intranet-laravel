<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use AcMarche\MailingList\Mail\NewsletterMail;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class PreviewAction
{
    public static function make(Email|Model $record): Action
    {
        return Action::make('preview')
            ->label('Apercu')
            ->icon(Heroicon::Eye)
            ->color('warning')
            ->modalHeading('Envoyer un apercu')
            ->schema([
                TextInput::make('email')
                    ->label('Adresse e-mail')
                    ->email()
                    ->required()
                    ->default(fn (): ?string => auth()->user()?->email),
            ])
            ->action(function (array $data) use ($record): void {
                $record->load('sender');

                $recipient = new EmailRecipient([
                    'email_address' => $data['email'],
                    'name' => 'Apercu',
                ]);

                $record->sender->resolveMailer()
                    ->to($data['email'])
                    ->send(new NewsletterMail($record, $recipient));

                Notification::make()
                    ->title('Apercu envoyé')
                    ->body("Un e-mail de test a été envoyé à {$data['email']}.")
                    ->success()
                    ->send();
            });

    }
}
