<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Pages;

use AcMarche\Conseil\Filament\Resources\Agendas\AgendaResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateAgenda extends CreateRecord
{
    #[Override]
    protected static string $resource = AgendaResource::class;

    public function getTitle(): string
    {
        return 'Nouvel ordre du jour';
    }
}
