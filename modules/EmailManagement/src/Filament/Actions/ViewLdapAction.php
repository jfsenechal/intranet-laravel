<?php

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ViewLdapAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('viewLdap')
            ->label('Voir la fiche LDAP')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->modalHeading('Entrée LDAP')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer')
            ->schema(function (EmployeLdapRepository $ldapEmployeRepository) use ($record): array {
                $ldapEntry = $ldapEmployeRepository->getEntry($record->samaccountname);

                if (!$ldapEntry) {
                    return [
                        TextEntry::make('error')
                            ->label('Erreur')
                            ->state('Entrée LDAP introuvable'),
                    ];
                }

                $attributes = [
                    'dn' => $ldapEntry->getDn(),
                    'sAMAccountName' => $ldapEntry->getFirstAttribute('samaccountname'),
                    'userPrincipalName' => $ldapEntry->getFirstAttribute('userprincipalname'),
                    'mail' => $ldapEntry->getFirstAttribute('mail'),
                    'cn' => $ldapEntry->getFirstAttribute('cn'),
                    'displayName' => $ldapEntry->getFirstAttribute('displayname'),
                    'givenName' => $ldapEntry->getFirstAttribute('givenname'),
                    'sn' => $ldapEntry->getFirstAttribute('sn'),
                    'telephoneNumber' => $ldapEntry->getFirstAttribute('telephonenumber'),
                    'description' => $ldapEntry->getFirstAttribute('description'),
                    'userAccountControl' => $ldapEntry->getFirstAttribute('useraccountcontrol'),
                    'whenCreated' => $ldapEntry->getFirstAttribute('whencreated'),
                ];

                $entries = [];
                foreach ($attributes as $key => $value) {
                    $displayValue = is_array($value) ? implode(', ', $value) : ($value ?? '-');
                    $entries[] = TextEntry::make($key)
                        ->label($key)
                        ->state($displayValue);
                }

                return [
                    Section::make('Attributs LDAP')
                        ->schema($entries)
                        ->columns(2),
                ];
            });
    }
}
