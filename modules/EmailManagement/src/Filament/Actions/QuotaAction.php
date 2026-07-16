<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use LdapRecord\LdapRecordException;

final class QuotaAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('changeQuota')
            ->label('Changer le quota')
            ->icon(Heroicon::CircleStack)
            ->color('info')
            ->modalHeading("Quota de {$record->samaccountname}")
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(function (EmployeLdapRepository $employeLdapRepository) use ($record): array {
                $ldapEntry = $employeLdapRepository->getEntry($record->samaccountname);

                return [
                    'quota' => $ldapEntry instanceof EmployeLdap
                        ? $employeLdapRepository->getQuota($ldapEntry)
                        : null,
                ];
            })
            ->schema([
                TextInput::make('quota')
                    ->label('Nouveau quota')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix('Mo')
                    ->helperText("En Mo. Laissé vide dans l'annuaire, le serveur applique sa valeur par défaut."),
            ])
            ->action(function (array $data, EmployeHandler $employeHandler) use ($record): void {
                try {
                    $employeHandler->setQuota($record, (float) $data['quota']);

                    Notification::make()
                        ->title('Le quota a bien été modifié')
                        ->success()
                        ->send();
                } catch (Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title('Impossible de changer le quota')
                        ->body(EmployeLdap::describe($e))
                        ->danger()
                        ->send();
                }
            });
    }
}
