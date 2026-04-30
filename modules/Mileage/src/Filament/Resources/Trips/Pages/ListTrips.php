<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\PersonalInformation\PersonalInformationResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Override;

final class ListTrips extends ListRecords
{
    #[Override]
    protected static string $resource = TripResource::class;

    public function getTitle(): string
    {
        return 'Liste de mes déplacements';
    }

    public function getSubheading(): Htmlable
    {
        return new HtmlString('Pour créer une nouvelle déclaration, cochez les déplacements <strong>non déclarés</strong>');
    }

    protected function getHeaderActions(): array
    {
        $userHasPersonalInfo = PersonalInformationRepository::getByCurrentUser()->exists();

        return [
            CreateAction::make()
                ->label('Nouveau déplacement')
                ->icon('tabler-plus')
                ->disabled(! $userHasPersonalInfo)
                ->tooltip($userHasPersonalInfo ? null : 'Vous devez d\'abord compléter vos informations personnelles')
                ->url($userHasPersonalInfo ? null : PersonalInformationResource::getUrl('index')),
        ];
    }
}
