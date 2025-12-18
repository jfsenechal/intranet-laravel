<?php

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListTrips extends ListRecords
{
    protected static string $resource = TripResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Liste de mes déplacements';
    }

    protected function getHeaderActions(): array
    {
        $userHasPersonalInfo = PersonalInformationRepository::getByCurrentUser()->exists();

        return [
            Actions\CreateAction::make()
                ->label('Nouveau déplacement')
                ->icon('tabler-plus')

                ->disabled(! $userHasPersonalInfo)
                ->tooltip(! $userHasPersonalInfo ? 'Vous devez d\'abord compléter vos informations personnelles' : null)
                ->url(! $userHasPersonalInfo ? PersonalInformationResource::getUrl('index') : null),
        ];
    }
}
