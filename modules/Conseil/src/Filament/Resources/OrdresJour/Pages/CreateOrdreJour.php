<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour\Pages;

use AcMarche\Conseil\Filament\Resources\OrdresJour\OrdreJourResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOrdreJour extends CreateRecord
{
    #[Override]
    protected static string $resource = OrdreJourResource::class;

    public function getTitle(): string
    {
        return 'Nouvel ordre du jour';
    }
}
