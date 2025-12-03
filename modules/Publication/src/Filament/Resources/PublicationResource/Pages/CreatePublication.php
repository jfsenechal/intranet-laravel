<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Pages;

use AcMarche\Publication\Filament\Resources\PublicationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePublication extends CreateRecord
{
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
