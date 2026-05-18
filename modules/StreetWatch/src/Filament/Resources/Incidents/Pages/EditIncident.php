<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Pages;

use AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditIncident extends EditRecord
{
    #[Override]
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
