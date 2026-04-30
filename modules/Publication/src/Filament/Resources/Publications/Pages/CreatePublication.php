<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Publications\Pages;

use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreatePublication extends CreateRecord
{
    #[Override]
    protected static string $resource = PublicationResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une publication';
    }
}
