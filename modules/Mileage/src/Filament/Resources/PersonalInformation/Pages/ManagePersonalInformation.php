<?php

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Models\PersonalInformation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePersonalInformation extends ManageRecords
{
    protected static string $resource = PersonalInformationResource::class;

    protected function getHeaderActions(): array
    {
        $userHasRecord = PersonalInformation::where('username', auth()->user()?->username)->exists();

        return [
            CreateAction::make()
                ->visible(! $userHasRecord),
        ];
    }
}
