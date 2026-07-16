<?php

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use LdapRecord\LdapRecordException;

class SyncFromLdapAction
{
    public static function make(): Action
    {
        return Action::make('syncFromLdap')
            ->label('Synchroniser')
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading("Synchroniser depuis l'annuaire")
            ->modalDescription(
                "Les fiches locales seront alignées sur Active Directory. Les fiches absentes de l'annuaire seront supprimées."
            )
            ->action(function (EmployeHandler $employeHandler): void {
                try {
                    $count = $employeHandler->syncFromLdap();

                    Notification::make()
                        ->title("{$count} employés synchronisés")
                        ->success()
                        ->send();
                } catch (\Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title('Erreur de synchronisation')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
