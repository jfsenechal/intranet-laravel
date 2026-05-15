<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Office\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Office\OfficeResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOffice extends CreateRecord
{
    #[Override]
    protected static string $resource = OfficeResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un guichet';
    }
}
