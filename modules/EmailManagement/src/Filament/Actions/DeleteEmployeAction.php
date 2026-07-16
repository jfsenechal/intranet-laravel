<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use LdapRecord\LdapRecordException;
use Livewire\Features\SupportRedirects\HandlesRedirects;

final class DeleteEmployeAction
{
    use HandlesRedirects;

    public static function make(Model|Employe $record): Action
    {
        return Action::make('deleteEmploye')
            ->label('Supprimer')
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("Supprimer l'employé")
            ->modalDescription(
                "Êtes-vous sûr de vouloir supprimer le compte de {$record->samaccountname} ? Cette action supprimera l'entrée dans l'annuaire et la fiche locale."
            )
            ->action(function (EmployeLdapRepository $ldapEmployeRepository) use ($record): void {
                try {
                    $ldapEmployeRepository->delete($record->samaccountname);
                    $record->delete();

                    Notification::make()
                        ->title('Compte supprimé avec succès')
                        ->success()
                        ->send();

                    $this->redirect(EmployeResource::getUrl());
                } catch (Exception|LdapRecordException $e) {
                    Notification::make()
                        ->title(EmployeLdap::describe($e))
                        ->danger()
                        ->send();
                }
            });
    }
}
