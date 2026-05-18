<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Pages;

use AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListIncidents extends ListRecords
{
    #[Override]
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel incident')
                ->icon(Heroicon::Plus),
        ];
    }
}
