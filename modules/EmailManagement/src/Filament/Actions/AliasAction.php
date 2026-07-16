<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use LdapRecord\LdapRecordException;

final class AliasAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('changeAlias')
            ->label('Gérer les alias')
            ->icon(Heroicon::AtSymbol)
            ->color('info')
            ->modalHeading("Alias de {$record->samaccountname}")
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(function (EmployeLdapRepository $employeLdapRepository) use ($record): array {
                $ldapEntry = $employeLdapRepository->getEntry($record->samaccountname);

                return [
                    'aliases' => $ldapEntry instanceof EmployeLdap
                        ? $employeLdapRepository->getAliases($ldapEntry)
                        : [],
                ];
            })
            ->schema([
                TagsInput::make('aliases')
                    ->label('Adresses alias')
                    ->placeholder('prenom.nom@ac.marche.be')
                    ->nestedRecursiveRules(['email'])
                    ->helperText('Adresses supplémentaires qui délivrent vers cette boîte. Enregistrer avec la liste vide retire tous les alias.'),
            ])
            ->action(function (array $data, EmployeHandler $employeHandler) use ($record): void {
                try {
                    $employeHandler->updateAliases($record, $data['aliases'] ?? []);

                    Notification::make()
                        ->title('Les alias ont bien été modifiés')
                        ->success()
                        ->send();
                } catch (Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title('Impossible de modifier les alias')
                        ->body(EmployeLdap::describe($e))
                        ->danger()
                        ->send();
                }
            });
    }
}
