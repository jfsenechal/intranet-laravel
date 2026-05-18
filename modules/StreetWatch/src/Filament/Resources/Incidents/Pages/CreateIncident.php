<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Pages;

use AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateIncident extends CreateRecord
{
    #[Override]
    protected static string $resource = IncidentResource::class;

    public function getTitle(): string
    {
        return 'Nouvel incident';
    }
}
