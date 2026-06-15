<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Filament\Resources\Employes\Schemas\EmployeForm;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Models\Employe;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use LdapRecord\LdapRecordException;

final class CreateEmploye extends CreateRecord
{
    protected static string $resource = EmployeResource::class;

    public function form(Schema $schema): Schema
    {
        return EmployeForm::forCreating($schema);
    }

    protected function handleRecordCreation(array $data): Employe
    {
        $citoyenHandler = app(EmployeHandler::class);

        try {
            $citoyen = $citoyenHandler->createEmploye($data);
        } catch (Exception|LdapRecordException $exception) {
            $error = $exception->getMessage();
            if ($exception instanceof LdapRecordException && $exception->getDetailedError()) {
                $error .= ' '.$exception->getDetailedError()->getDiagnosticMessage();
            }

            Notification::make()
                ->title('Erreur lors de la création')
                ->body($error)
                ->danger()
                ->send();

            $this->halt();
        }

        Notification::make()
            ->title('Le citoyen a bien été ajouté.')
            ->body($body)
            ->success()
            ->persistent()
            ->send();

        return $citoyen;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }
}
