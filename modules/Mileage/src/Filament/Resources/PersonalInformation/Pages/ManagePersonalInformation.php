<?php

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePersonalInformation extends ManageRecords
{
    protected static string $resource = PersonalInformationResource::class;

    protected function getHeaderActions(): array
    {
        $userHasRecord = PersonalInformationRepository::getByCurrentUser()->exists();

        return [
            CreateAction::make()
                ->visible(! $userHasRecord),
        ];
    }
}
