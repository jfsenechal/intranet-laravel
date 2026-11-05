<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Service\EmailService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use LdapRecord\LdapRecordException;

final class CreateEmailAction
{
    /**
     * @param  array{samaccountname: string, mail: ?string, givenName: ?string, sn: ?string}  $record
     */
    public static function make(array $record): Action
    {
        return Action::make('createEmail')
            ->label($record['mail'] ? "Changer l'adresse" : 'Créer une adresse')
            ->icon(Heroicon::Envelope)
            ->color('warning')
            ->modalHeading("Adresse mail de {$record['samaccountname']}")
            ->modalDescription(
                "Si il s'agit d'une adresse mail déjà existante, vous pouvez forcer la création du dossier Imap"
            )
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(function () use ($record): array {
                $mail = $record['mail'] ?? self::suggestLocalPart($record);

                return [
                    'mail' => EmailExtensionEnum::localPart($mail),
                    'extension' => (EmailExtensionEnum::fromAddress(
                        $record['mail']
                    ) ?? EmailExtensionEnum::EXTENSION_AC)->value,
                    'force' => false,
                ];
            })
            ->schema(self::addressFields())
            ->action(function (array $data, EmployeHandler $employeHandler) use ($record): void {
                try {
                    $mailboxCreated = $employeHandler->createEmail(
                        $record['samaccountname'],
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
     * @return array<int, Section>
     */
    private static function addressFields(): array
    {
        return [
            Section::make('Adresse')
                ->columns()
                ->components([
                    TextInput::make('mail')
                        ->label('Adresse mail')
                        ->required()
                        ->maxLength(200)
                        ->rule('regex:/^[A-Za-z0-9._%+-]+$/')
                        ->validationMessages([
                            'regex' => "L'adresse ne peut pas contenir de @ : le domaine se choisit à côté.",
                        ])
                        ->helperText('Sans le domaine, par exemple prenom.nom'),
                    Select::make('extension')
                        ->label('Domaine')
                        ->options(EmailExtensionEnum::class)
                        ->default(EmailExtensionEnum::EXTENSION_AC)
                        ->selectablePlaceholder(false)
                        ->required(),
                    Checkbox::make('force')
                        ->label('Forcer')
                        ->helperText(
                            "Enregistrer même si l'adresse est déjà utilisée par une autre fiche de l'annuaire."
                        )
                        ->columnSpanFull(),
                ]),
        ];
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
     *
     * @param  array{givenName: ?string, sn: ?string}  $record
     */
    private static function suggestLocalPart(array $record): ?string
    {
        if (blank($record['givenName']) || blank($record['sn'])) {
            return null;
        }

        return EmailService::sanitizeForEmail($record['givenName'])
            .'.'.EmailService::sanitizeForEmail($record['sn']);
    }
}
