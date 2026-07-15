<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateTrip extends CreateRecord
{
    #[Override]
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

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Ajouter');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Ajouter & en ajouter un autre');
    }
}
