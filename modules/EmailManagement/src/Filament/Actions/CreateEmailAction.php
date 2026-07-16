<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Service\EmailService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use LdapRecord\LdapRecordException;

final class CreateEmailAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('createEmail')
            ->label($record->mail ? "Changer l'adresse" : 'Créer une adresse')
            ->icon(Heroicon::Envelope)
            ->color('info')
            ->modalHeading("Adresse mail de {$record->samaccountname}")
            ->modalDescription(
                "L'adresse est écrite dans l'annuaire, le quota par défaut est appliqué et la boîte est créée si elle n'existe pas."
            )
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(fn (): array => [
                'mail' => $record->mail ?? self::suggestAddress($record),
                'force' => false,
            ])
            ->schema([
                TextInput::make('mail')
                    ->label('Adresse mail')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Checkbox::make('force')
                    ->label('Forcer')
                    ->helperText("Enregistrer même si l'adresse est déjà utilisée par une autre fiche de l'annuaire."),
            ])
            ->action(function (array $data, EmployeHandler $employeHandler) use ($record): void {
                try {
                    $mailboxCreated = $employeHandler->createEmail(
                        $record,
                        $data['mail'],
                        (bool) ($data['force'] ?? false),
                    );
                } catch (Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title("Impossible d'enregistrer l'adresse")
                        ->body(EmployeLdap::describe($e))
                        ->danger()
                        ->send();

                    return;
                }

                if (! $mailboxCreated) {
                    Notification::make()
                        ->title('Adresse enregistrée, boîte non créée')
                        ->body('Les identifiants IMAP ne sont pas configurés (IMAP_EMPLOYE_*). La boîte devra être créée séparément.')
                        ->warning()
                        ->persistent()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title("L'adresse et la boîte ont bien été créées")
                    ->success()
                    ->send();
            });
    }

    /**
     * prenom.nom@ac.marche.be, as the legacy GestEmail built it.
     */
    private static function suggestAddress(Model|Employe $record): ?string
    {
        if (blank($record->givenName) || blank($record->sn)) {
            return null;
        }

        return EmailService::sanitizeForEmail($record->givenName)
            .'.'.EmailService::sanitizeForEmail($record->sn)
            .'@ac.marche.be';
    }
}
