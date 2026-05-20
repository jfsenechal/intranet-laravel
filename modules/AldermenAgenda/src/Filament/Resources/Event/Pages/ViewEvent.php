<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Event\EventResource;
use AcMarche\AldermenAgenda\Filament\Resources\Event\Schemas\EventInfolist;
use AcMarche\AldermenAgenda\Mail\EventEmail;
use AcMarche\AldermenAgenda\Models\Event;
use AcMarche\AldermenAgenda\Models\Recipient;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use Override;

final class ViewEvent extends ViewRecord
{
    #[Override]
    protected static string $resource = EventResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Envoyer un aperçu')
                ->icon('tabler-eye')
                ->color('warning')
                ->modalHeading('Envoyer un aperçu de l\'événement')
                ->schema([
                    TextInput::make('email')
                        ->label('Adresse e-mail')
                        ->email()
                        ->required()
                        ->default(fn (): ?string => auth()->user()?->email),
                ])
                ->action(function (array $data, Event $record): void {
                    Mail::to($data['email'])
                        ->send(new EventEmail($record, isPreview: true));

                    Notification::make()
                        ->title('Aperçu envoyé')
                        ->body("Un aperçu a été envoyé à {$data['email']}.")
                        ->success()
                        ->send();
                }),
            Action::make('sendToAll')
                ->label('Envoyer aux destinataires')
                ->icon('tabler-send')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Envoyer l\'événement aux destinataires')
                ->modalDescription('L\'événement sera envoyé par e-mail à tous les destinataires.')
                ->action(function (Event $record): void {
                    $recipients = Recipient::query()
                        ->whereNotNull('email')
                        ->pluck('email');

                    if ($recipients->isEmpty()) {
                        Notification::make()
                            ->title('Aucun destinataire')
                            ->body('Aucun destinataire avec une adresse e-mail n\'a été trouvé.')
                            ->warning()
                            ->send();

                        return;
                    }

                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new EventEmail($record));
                    }

                    $record->update(['sent' => true]);

                    Notification::make()
                        ->title('Événement envoyé')
                        ->body("L'événement a été envoyé à {$recipients->count()} destinataire(s).")
                        ->success()
                        ->send();
                }),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
