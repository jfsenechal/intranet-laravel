<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages;

use AcMarche\StreetWatch\Filament\Resources\TypesIncident\TypeIncidentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListTypesIncident extends ListRecords
{
    #[Override]
    protected static string $resource = TypeIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label("Nouveau type d'incident")
                ->icon(Heroicon::Plus),
        ];
    }
}
