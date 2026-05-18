<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages;

use AcMarche\StreetWatch\Filament\Resources\TypesIncident\TypeIncidentResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateTypeIncident extends CreateRecord
{
    #[Override]
    protected static string $resource = TypeIncidentResource::class;

    public function getTitle(): string
    {
        return "Nouveau type d'incident";
    }
}
