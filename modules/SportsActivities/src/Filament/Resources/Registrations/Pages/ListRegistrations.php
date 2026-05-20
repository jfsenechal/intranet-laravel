<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Pages;

use AcMarche\SportsActivities\Filament\Resources\Registrations\RegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListRegistrations extends ListRecords
{
    #[Override]
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle inscription')
                ->icon(Heroicon::Plus),
        ];
    }
}
