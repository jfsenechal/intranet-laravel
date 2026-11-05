<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas\CitoyenForm;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Repository\CitoyenRepository;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

final class CreateCitoyen extends CreateRecord
{
    protected static string $resource = CitoyenResource::class;

    public function form(Schema $schema): Schema
    {
        return CitoyenForm::forCreating($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Citoyen
    {
        $citoyen = app(CitoyenRepository::class)->create($data);

        Notification::make()
            ->title('Le citoyen a bien été ajouté')
            ->body(view('email-management::filament.citoyens.created-notification', [
                'mail' => $citoyen->mail,
                'uid' => $citoyen->uid,
                'password' => $data['password'],
            ])->render())
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
