<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Actions\PasswordLdapAction;
use AcMarche\EmailManagement\Filament\Actions\ViewLdapAction;
use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use LdapRecord\LdapRecordException;

final class ViewEmploye extends ViewRecord
{
    protected static string $resource = EmployeResource::class;

    public function getTitle(): string
    {
        return $this->record->mail ?? 'Sans email';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->viewLdapAction(),
            ActionGroup::make([
                ViewLdapAction::make(),
                PasswordLdapAction::make($this->record),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::Pencil),
                $this->deleteAction(),
            ])
                ->label('Actions...')
                ->button()
                ->size(Size::Large)
                ->color('secondary'),
        ];
    }

    private function deleteAction(): Action
    {
        return Action::make('deleteEmploye')
            ->label('Supprimer')
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("Supprimer l'employé")
            ->modalDescription(
                "Êtes-vous sûr de vouloir supprimer le compte de {$this->record->samaccountname} ? Cette action supprimera l'entrée dans l'annuaire et la fiche locale."
            )
            ->action(function (EmployeLdapRepository $ldapEmployeRepository): void {
                try {
                    $ldapEmployeRepository->delete($this->record->samaccountname);
                    $this->record->delete();

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
