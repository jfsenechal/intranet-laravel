<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Exception;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditEmploye extends EditRecord
{
    protected static string $resource = EmployeResource::class;

    public function getTitle(): string
    {
        return $this->record->mail ?? 'Empty name';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }

    protected function afterSave(): void
    {
        $employeHandler = app(EmployeHandler::class);
        $ldapEmployeRepository = app(EmployeLdapRepository::class);
        $employe = $this->record;

        try {
            $ldapEntry = $ldapEmployeRepository->getEntry($employe->samaccountname);

            if (! $ldapEntry) {
                Notification::make()
                    ->title('Utilisateur LDAP introuvable pour '.$employe->samaccountname)
                    ->warning()
                    ->send();

                return;
            }

            $employeHandler->updateEmploye($employe, $ldapEntry);

            Notification::make()
                ->title('Entrée LDAP mise à jour')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur LDAP: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
