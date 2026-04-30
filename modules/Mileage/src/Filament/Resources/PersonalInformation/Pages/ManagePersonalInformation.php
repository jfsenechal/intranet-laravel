<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Override;

final class ManagePersonalInformation extends ManageRecords
{
    #[Override]
    protected static string $resource = PersonalInformationResource::class;

    #[Override]
    protected static ?string $title = 'Mes informations personnelles';

    #[Override]
    protected ?string $subheading = 'Ces informations sont nécessaires à votre déclaration.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter mes informations')
                ->icon('tabler-plus')
                ->createAnother(false),
        ];
    }
}
