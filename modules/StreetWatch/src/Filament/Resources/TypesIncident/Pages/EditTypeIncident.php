<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages;

use AcMarche\StreetWatch\Filament\Resources\TypesIncident\TypeIncidentResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditTypeIncident extends EditRecord
{
    #[Override]
    protected static string $resource = TypeIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
