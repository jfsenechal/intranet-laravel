<?php

namespace AcMarche\Mileage\Filament\Resources\Trip\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Filament\Resources\TripResource;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateTrip extends CreateRecord
{
    protected static string $resource = TripResource::class;

    public function mount(): void
    {
        if (! PersonalInformationRepository::getByCurrentUser()->exists()) {
            Notification::make()
                ->warning()
                ->title('Informations personnelles requises')
                ->body('Vous devez d\'abord compléter vos informations personnelles avant de créer un déplacement.')
                ->persistent()
                ->send();

            $this->redirect(PersonalInformationResource::getUrl('index'));
        }

        parent::mount();
    }

    public function getTitle(): string
    {
        return 'Ajouter un déplacement';
    }
}
