<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use AcMarche\EmailManagement\Filament\Resources\Employes\Schemas\EmployeForm;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Service\EmailService;
use Exception;
use Filament\Actions\Action;
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
                "Si il s'agit d'une adresse mail déjà existante, vous pouvez forcer la création du dossier Imap"
            )
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(function () use ($record): array {
                $mail = $record->mail ?? self::suggestLocalPart($record);

                return [
                    'mail' => EmailExtensionEnum::localPart($mail),
                    'extension' => (EmailExtensionEnum::fromAddress(
                        $record->mail
                    ) ?? EmailExtensionEnum::EXTENSION_AC)->value,
                    'force' => false,
                ];
            })
            ->schema(EmployeForm::forEmail())
            ->action(function (array $data, EmployeHandler $employeHandler) use ($record): void {
                try {
                    $mailboxCreated = $employeHandler->createEmail(
                        $record,
                        $data['mail'].self::extensionValue($data['extension']),
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
                        ->body(
                            'Les identifiants IMAP ne sont pas configurés (IMAP_EMPLOYE_*). La boîte devra être créée séparément.'
                        )
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
     * The select hands back the enum case itself once the schema has cast it, and the raw
     * string before that, so both have to be accepted.
     */
    private static function extensionValue(EmailExtensionEnum|string $extension): string
    {
        return $extension instanceof EmailExtensionEnum ? $extension->value : $extension;
    }

    /**
     * prenom.nom, as the legacy GestEmail built it. The domain is picked separately.
     */
    private static function suggestLocalPart(Model|Employe $record): ?string
    {
        if (blank($record->givenName) || blank($record->sn)) {
            return null;
        }

        return EmailService::sanitizeForEmail($record->givenName)
            .'.'.EmailService::sanitizeForEmail($record->sn);
    }
}
