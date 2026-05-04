<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Archive\ArchiveResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateArchive extends CreateRecord
{
    #[Override]
    protected static string $resource = ArchiveResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une archive';
    }
}
