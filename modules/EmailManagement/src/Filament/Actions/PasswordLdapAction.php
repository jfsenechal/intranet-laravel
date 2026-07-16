<?php

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;
use LdapRecord\LdapRecordException;

class PasswordLdapAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('changePassword')
            ->label('Changer le mot de passe')
            ->icon(Heroicon::Key)
            ->color('info')
            ->modalHeading("Changer le mot de passe de {$record->samaccountname}")
            ->schema([
                TextInput::make('password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised())
                    ->confirmed()
                    ->helperText('12 caractères minimum, avec majuscules, minuscules, chiffres et symboles.'),
                TextInput::make('password_confirmation')
                    ->label('Confirmer le mot de passe')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
            ])
            ->action(function (array $data, EmployeLdapRepository $ldapEmployeRepository) use ($record): void {
                try {
                    $ldapEntry = $ldapEmployeRepository->getEntry($record->samaccountname);

                    if (!$ldapEntry) {
                        Notification::make()
                            ->title('Entrée LDAP introuvable')
                            ->danger()
                            ->send();

                        return;
                    }

                    $ldapEntry->unicodepwd = $data['password'];
                    $ldapEntry->save();

                    Notification::make()
                        ->title('Mot de passe modifié avec succès')
                        ->success()
                        ->send();
                } catch (\Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title(EmployeLdap::describe($e))
                        ->danger()
                        ->send();
                }
            });
    }

}
